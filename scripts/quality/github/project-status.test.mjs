import assert from "node:assert/strict";
import test from "node:test";

import { runDeliveryStatus } from "../../github/project-status.mjs";

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
