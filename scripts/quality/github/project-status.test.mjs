import assert from "node:assert/strict";
import test from "node:test";

import { runDeliveryStartNext, runDeliveryStatus } from "../../github/project-status.mjs";

const repo = "spardobo/deturistaando";
const states = ["Backlog", "Ready", "Active", "Review", "Verify", "Done", "Blocked"];
const statusField = () => ({
    id: "FIELD",
    name: "Status",
    type: "ProjectV2SingleSelectField",
    options: states.map((name) => ({ id: name, name })),
});
const item = (status, number = 42, repository = repo) => ({
    id: `P${number}`,
    content: { number, repository },
    status,
});
const base = (extra = {}) => ({
    issue: { id: "I1", number: 42, labels: [{ name: "status:approved" }] },
    items: [item("Backlog")],
    prs: {},
    edits: 0,
    ...extra,
});
const move = (from, to, extra = []) => ["--issue", "42", "--from", from, "--to", to, ...extra];

function fakeGh(data) {
    return async (args) => {
        data.calls ??= [];
        data.calls.push(args);
        const [group, command] = args;
        if (group === "issue") return JSON.stringify(data.issue);
        if (group === "pr") return JSON.stringify(data.prs[args[2]] ?? null);
        if (group !== "project") throw new Error("Unexpected gh command");
        if (command === "view") return JSON.stringify({ id: "PROJECT" });
        if (command === "field-list")
            return JSON.stringify({ fields: data.fields ?? [statusField()] });
        if (command === "item-list")
            return JSON.stringify({
                items: data.items,
                totalCount: data.totalCount ?? data.items.length,
            });
        if (command === "item-edit") {
            data.edits += 1;
            if (!data.stale) data.items[0].status = args.at(-1);
            return JSON.stringify({ id: "P42" });
        }
        throw new Error("Unexpected project command");
    };
}

async function run(data, args) {
    const output = [];
    const result = await runDeliveryStatus(args, {
        gh: fakeGh(data),
        write: (line) => output.push(line),
    });
    return { result, output: output.join("\n") };
}

const itemListsUseLimit = (data) =>
    data.calls.filter((args) => args[1] === "item-list").every((args) => args.includes("1000"));

const waveField = () => ({
    id: "WAVE",
    name: "Wave",
    type: "ProjectV2SingleSelectField",
    options: [{ id: "W1", name: "Wave 1" }],
});
const draftItem = (extra = {}) => ({
    id: "PD1",
    content: { type: "DraftIssue", title: "Next slice", body: "Body" },
    status: "Backlog",
    wave: "Wave 1",
    ...extra,
});
const startBase = (extra = {}) => ({
    items: [draftItem()],
    fields: [statusField(), waveField()],
    repository: { id: "REPO", nameWithOwner: repo },
    issue: {
        number: 71,
        url: "https://github.com/spardobo/deturistaando/issues/71",
        labels: [],
    },
    conversions: 0,
    labels: 0,
    moves: 0,
    ...extra,
});
const applyStart = ["--item", "PD1", "--wave", "Wave 1", "--apply", "--confirm-human-gate"];

function fakeStart(data) {
    return async (args) => {
        data.calls ??= [];
        data.calls.push(args);
        const [group, command] = args;
        if (group === "repo") return JSON.stringify(data.repository);
        if (group === "issue" && command === "edit") {
            if (data.labelFailure) throw new Error("label failed");
            data.labels += 1;
            data.issue.labels = [{ name: "status:approved" }];
            return "";
        }
        if (group === "issue") return JSON.stringify(data.issue);
        if (group === "api") {
            data.conversions += 1;
            if (data.conversionFailure) throw new Error("network failed");
            data.items[0].content = {
                ...data.items[0].content,
                type: "Issue",
                number: 71,
                repository: repo,
            };
            return JSON.stringify({
                data: { convertProjectV2DraftIssueItemToIssue: { item: { id: "PD1" } } },
            });
        }
        if (group !== "project") throw new Error("Unexpected gh command");
        if (command === "view") return JSON.stringify({ id: "PROJECT" });
        if (command === "field-list") {
            data.fieldReads = (data.fieldReads ?? 0) + 1;
            if (data.fieldReads === 2) data.freshFieldHook?.(data);
            return JSON.stringify({ fields: data.fields });
        }
        if (command === "item-list") {
            data.itemReads = (data.itemReads ?? 0) + 1;
            if (data.itemReads === 2) data.freshHook?.(data);
            return JSON.stringify({ items: data.items, totalCount: data.items.length });
        }
        if (command === "item-edit") {
            data.moves += 1;
            if (data.moveFailure === data.moves) throw new Error("move failed");
            data.items[0].status = args.at(-1);
            return JSON.stringify({ id: "PD1" });
        }
        throw new Error("Unexpected project command");
    };
}

async function runStart(data, args = ["--item", "PD1", "--wave", "Wave 1"]) {
    const output = [];
    const result = await runDeliveryStartNext(args, {
        gh: fakeStart(data),
        write: (line) => output.push(line),
    });
    return { result, output: output.join("\n") };
}

test("uses live field and item shapes, limits item reads, and reads back apply", async () => {
    const dry = base({ items: [item("Backlog"), item("Active", 42, "other/repo")] });
    const result = await run(dry, move("Backlog", "Ready"));
    assert.equal(dry.edits, 0);
    assert.equal(result.result.mode, "dry-run");
    assert.match(result.output, /dry-run[\s\S]*confirmation/i);
    assert.ok(itemListsUseLimit(dry));

    const apply = base({ items: [item("Ready")] });
    assert.match(
        (await run(apply, move("Ready", "Active", ["--apply", "--confirm-human-gate"]))).output,
        /Readback: Active/,
    );
    assert.equal(apply.edits, 1);
    assert.ok(itemListsUseLimit(apply));
});

test("rejects transition, membership, approval, WIP, and human-gate failures", async () => {
    await assert.rejects(run(base(), move("Backlog", "Active")), /Unsupported/);
    await assert.rejects(run(base(), move("Ready", "Active")), /not Ready/);
    for (const items of [[], [item("Backlog"), item("Backlog")]]) {
        await assert.rejects(run(base({ items }), move("Backlog", "Ready")), /exactly one/);
    }
    await assert.rejects(
        run(base({ issue: { id: "I1", number: 42, labels: [] } }), move("Backlog", "Ready")),
        /status:approved/,
    );
    await assert.rejects(
        run(base({ items: [item("Ready"), item("Active", 43)] }), move("Ready", "Active")),
        /WIP/,
    );
    for (const [from, to] of [
        ["Backlog", "Ready"],
        ["Ready", "Active"],
        ["Verify", "Done"],
    ]) {
        await assert.rejects(
            run(base({ items: [item(from)] }), move(from, to, ["--apply"])),
            /confirm-human-gate/,
        );
    }
});

test("fails closed on truncated items and duplicate Status options", async () => {
    await assert.rejects(run(base({ totalCount: 2 }), move("Backlog", "Ready")), /items/);
    for (const name of ["Backlog", "Ready"]) {
        const duplicate = {
            ...statusField(),
            options: [...statusField().options, { id: `${name}-2`, name }],
        };
        await assert.rejects(
            run(base({ fields: [duplicate] }), move("Backlog", "Ready")),
            /Status value/,
        );
    }
});

test("accepts colon references and rejects unmerged or intermediate Verify PRs", async () => {
    const review = base({
        items: [item("Active")],
        prs: { 7: { state: "OPEN", body: "Refs: #42" } },
    });
    assert.match((await run(review, move("Active", "Review", ["--pr", "7"]))).output, /PR #7/);
    const verify = base({
        items: [item("Review")],
        prs: { 9: { state: "MERGED", baseRefName: "main", body: "Closes: #42" } },
    });
    assert.match((await run(verify, move("Review", "Verify", ["--pr", "9"]))).output, /PR #9/);
    for (const pr of [
        { state: "OPEN", baseRefName: "main", body: "Closes #42" },
        { state: "MERGED", baseRefName: "stack", body: "Closes #42" },
        { state: "MERGED", baseRefName: "main", body: "References #42" },
    ]) {
        await assert.rejects(
            run(
                base({ items: [item("Review")], prs: { 8: pr } }),
                move("Review", "Verify", ["--pr", "8"]),
            ),
            /evidence/,
        );
    }
});

test("fails closed on stale Project readback", async () => {
    await assert.rejects(
        run(
            base({ items: [item("Ready")], stale: true }),
            move("Ready", "Active", ["--apply", "--confirm-human-gate"]),
        ),
        /readback/i,
    );
});

test("start-next dry-run is immutable and describes the bounded transaction", async () => {
    const data = startBase();
    const { result, output } = await runStart(data);
    assert.equal(result.mode, "dry-run");
    assert.equal(data.conversions, 0);
    assert.equal(data.labels, 0);
    assert.equal(data.moves, 0);
    assert.match(
        output,
        /PD1[\s\S]*Wave 1[\s\S]*Backlog → Ready → Active[\s\S]*WIP=0[\s\S]*without creating a duplicate/,
    );
});

test("start-next rejects invalid candidates, fields, WIP, and an absent human gate", async () => {
    await assert.rejects(runDeliveryStartNext([]), /--item/);
    for (const data of [
        startBase({ items: [] }),
        startBase({ items: [draftItem(), draftItem()] }),
        startBase({
            items: [draftItem({ content: { type: "Issue", title: "Next slice", body: "Body" } })],
        }),
        startBase({ items: [draftItem({ status: "Ready" })] }),
        startBase({ items: [draftItem({ wave: "Wave 2" })] }),
        startBase({ items: [draftItem(), draftItem({ id: "PD2", status: "Active" })] }),
        startBase({ fields: [statusField(), waveField(), waveField()] }),
    ])
        await assert.rejects(runStart(data), /missing|DraftIssue|Backlog|Wave|WIP|ambiguous/i);
    const data = startBase();
    await assert.rejects(
        runStart(data, ["--item", "PD1", "--wave", "Wave 1", "--apply"]),
        /confirmation/i,
    );
    assert.equal(data.conversions, 0);
});

test("start-next rejects stale preconditions before conversion", async () => {
    for (const freshHook of [
        (data) => (data.items[0].content.body = "Changed"),
        (data) => (data.repository.id = "OTHER"),
    ]) {
        const data = startBase({ freshHook });
        await assert.rejects(runStart(data, applyStart), /stale/i);
        assert.equal(data.conversions, 0);
    }
    const fields = startBase({
        freshFieldHook: (data) => (data.fields[0].id = "OTHER-FIELD"),
    });
    await assert.rejects(runStart(fields, applyStart), /stale/i);
    assert.equal(fields.conversions, 0);
});

test("start-next converts once, labels, and verifies Ready then Active", async () => {
    const data = startBase();
    const { result, output } = await runStart(data, applyStart);
    assert.deepEqual(result, {
        mode: "apply",
        number: 71,
        url: "https://github.com/spardobo/deturistaando/issues/71",
        itemId: "PD1",
    });
    assert.equal(data.conversions, 1);
    assert.equal(data.labels, 1);
    assert.equal(data.moves, 2);
    assert.equal(data.items[0].status, "Active");
    assert.match(output, /Issue: #71[\s\S]*Readback: Active[\s\S]*separately authorized/);
    assert.equal(
        data.calls.some((args) => args[0] === "issue" && args[1] === "create"),
        false,
    );
    assert.match(
        data.calls.find((args) => args[0] === "api").join(" "),
        /convertProjectV2DraftIssueItemToIssue/,
    );
});

test("start-next stops with the completed stage after indeterminate or partial failures", async () => {
    for (const scenario of [
        {
            data: startBase({ conversionFailure: true }),
            error: /indeterminate[\s\S]*inspect Project item PD1/i,
            counts: [1, 0, 0],
        },
        {
            data: startBase({ labelFailure: true }),
            error: /after conversion for issue #71/i,
            counts: [1, 0, 0],
        },
        {
            data: startBase({ moveFailure: 1 }),
            error: /after label for issue #71/i,
            counts: [1, 1, 1],
        },
        {
            data: startBase({ moveFailure: 2 }),
            error: /after Ready for issue #71/i,
            counts: [1, 1, 2],
        },
    ]) {
        await assert.rejects(runStart(scenario.data, applyStart), scenario.error);
        assert.deepEqual(
            [scenario.data.conversions, scenario.data.labels, scenario.data.moves],
            scenario.counts,
        );
    }
});
