#!/usr/bin/env node

import { spawn } from "node:child_process";

const repo = "spardobo/deturistaando";
const transitions = new Map([
    ["Backlog", "Ready"],
    ["Ready", "Active"],
    ["Active", "Review"],
    ["Review", "Verify"],
    ["Verify", "Done"],
]);
const project = (command) => ["project", command, "2", "--owner", "spardobo", "--format", "json"];
const fail = (message) => {
    throw new Error(message);
};
const matching = (items, issue) =>
    items.filter(
        (item) => item?.content?.number === issue.number && item.content.repository === repo,
    );
const issueReference = (issue, keywords) =>
    new RegExp(`\\b(?:${keywords})\\s*:?\\s*#${issue}\\b`, "i");
const closingReference = (issue) => issueReference(issue, "closes|fixes|resolves");
const intermediateReference = (issue) => issueReference(issue, "refs|references|part of");

function nativeGh(args) {
    return new Promise((resolve, reject) => {
        const child = spawn("gh", args, { shell: false });
        let output = "";
        let error = "";
        child.stdout.on("data", (chunk) => (output += chunk));
        child.stderr.on("data", (chunk) => (error += chunk));
        child.on("error", () => reject(new Error("Unable to execute gh.")));
        child.on("close", (code) =>
            code === 0 ? resolve(output) : reject(new Error(error.trim() || "gh command failed.")),
        );
    });
}

async function json(gh, args) {
    try {
        return JSON.parse(await gh(args));
    } catch {
        fail("GitHub command failed or returned malformed JSON.");
    }
}

function parse(argv) {
    const result = {};
    for (let index = 0; index < argv.length; index += 1) {
        const argument = argv[index];
        if (argument === "--apply") result.apply = true;
        else if (argument === "--confirm-human-gate") result.confirm = true;
        else if (["--issue", "--from", "--to", "--pr"].includes(argument))
            result[argument.slice(2)] = argv[++index];
        else fail(`Unknown argument: ${argument}`);
    }
    if (!result.issue || !result.from || !result.to)
        fail("Use --issue <N> --from <State> --to <State>.");
    for (const name of ["issue", "pr"])
        if (result[name] && (!/^\d+$/.test(result[name]) || Number(result[name]) < 1))
            fail(`--${name} must be a positive integer.`);
    return result;
}

async function board(gh) {
    const [data, fieldData] = await Promise.all([
        json(gh, project("view")),
        json(gh, project("field-list")),
    ]);
    const fields = fieldData?.fields;
    const status =
        Array.isArray(fields) &&
        fields.filter(
            (field) => field?.name === "Status" && field.type === "ProjectV2SingleSelectField",
        );
    if (!data?.id || status?.length !== 1 || !status[0].id || !Array.isArray(status[0].options))
        fail("Project #2 Status is missing or ambiguous.");
    return [data, status[0], fields];
}

async function items(gh) {
    const data = await json(gh, [...project("item-list"), "--limit", "1000"]);
    if (
        !Array.isArray(data?.items) ||
        !Number.isInteger(data.totalCount) ||
        data.totalCount < 0 ||
        data.totalCount !== data.items.length
    )
        fail("Project #2 items are missing, malformed, or incomplete.");
    return data.items;
}

function prStatusOptions(argv) {
    const result = {};
    for (let index = 0; index < argv.length; index += 1) {
        const argument = argv[index];
        if (!["--pr", "--issue", "--role"].includes(argument))
            fail(`Unknown argument: ${argument}`);
        const name = argument.slice(2);
        if (result[name]) fail(`Duplicate argument: ${argument}`);
        result[name] = argv[++index];
    }
    if (!result.pr || !result.issue || !result.role)
        fail("Use --pr <N> --issue <N> --role <intermediate|final>.");
    for (const name of ["pr", "issue"])
        if (!/^\d+$/.test(result[name]) || Number(result[name]) < 1)
            fail(`--${name} must be a positive integer.`);
    if (!["intermediate", "final"].includes(result.role))
        fail("--role must be intermediate or final.");
    return result;
}

function names(labels, owner) {
    if (!Array.isArray(labels) || labels.some((label) => typeof label?.name !== "string"))
        fail(`${owner} labels are malformed.`);
    return labels.map((label) => label.name);
}

function checks(rollup) {
    if (!Array.isArray(rollup) || rollup.some((check) => typeof check?.name !== "string"))
        fail("PR statusCheckRollup is malformed.");
    const successful = new Set(
        rollup
            .filter((check) => check.conclusion === "SUCCESS" || check.state === "SUCCESS")
            .map((check) => check.name),
    );
    if (!["policy", "quality"].every((name) => successful.has(name)))
        fail("Required policy and quality checks are not successful.");
}

function reviewBudget(pr) {
    if (![pr.additions, pr.deletions].every((value) => Number.isSafeInteger(value) && value >= 0))
        fail("PR additions or deletions are malformed.");
    const total = pr.additions + pr.deletions;
    if (!Number.isSafeInteger(total) || total > 400)
        fail("PR review budget exceeds 400; no size:exception route is configured.");
    return total;
}

export async function runDeliveryPrStatus(argv, { gh = nativeGh, write = console.log } = {}) {
    const options = prStatusOptions(argv);
    const repository = await json(gh, ["repo", "view", repo, "--json", "id,nameWithOwner"]);
    if (!repository?.id || repository.nameWithOwner !== repo)
        fail("Repository identity is unavailable.");

    const issue = await json(gh, [
        "issue",
        "view",
        options.issue,
        "--repo",
        repo,
        "--json",
        "id,number,labels",
    ]);
    if (
        !issue?.id ||
        issue.number !== Number(options.issue) ||
        names(issue.labels, "Issue").filter((name) => name === "status:approved").length !== 1
    )
        fail(`Issue #${options.issue} must exist and carry status:approved.`);

    const pr = await json(gh, [
        "pr",
        "view",
        options.pr,
        "--repo",
        repo,
        "--json",
        "number,state,isDraft,baseRefName,body,labels,statusCheckRollup,mergeable,mergeStateStatus,additions,deletions",
    ]);
    if (
        pr?.number !== Number(options.pr) ||
        pr.state !== "OPEN" ||
        pr.isDraft !== false ||
        pr.baseRefName !== "main"
    )
        fail("PR number, state, draft status, or base is invalid.");
    const expectedReference =
        options.role === "intermediate"
            ? intermediateReference(options.issue)
            : closingReference(options.issue);
    if (typeof pr.body !== "string" || !expectedReference.test(pr.body))
        fail("PR issue reference is invalid.");
    const type = names(pr.labels, "PR").filter((name) => name.startsWith("type:"));
    if (type.length !== 1) fail("PR must have exactly one type:* label.");
    checks(pr.statusCheckRollup);
    const budget = reviewBudget(pr);
    if (pr.mergeable !== "MERGEABLE" || pr.mergeStateStatus !== "CLEAN")
        fail("PR mergeability or merge state is not clean.");

    write(
        `Mode: read-only\nPR: #${pr.number}\nIssue approved: #${issue.number}\nRole: ${options.role}\nBase: ${pr.baseRefName}\nType: ${type[0]}\nChecks: policy, quality\nMerge: ${pr.mergeable}/${pr.mergeStateStatus}\nReview budget: ${budget}/400\nReadiness: ready`,
    );
    return { readiness: "ready" };
}

async function checkPr(gh, from, number, issue) {
    if (!number) fail(`${from} requires --pr <N>.`);
    const pr = await json(gh, [
        "pr",
        "view",
        number,
        "--repo",
        repo,
        "--json",
        "state,baseRefName,body",
    ]);
    const valid =
        from === "Active"
            ? pr?.state === "OPEN" &&
              (intermediateReference(issue).test(pr.body ?? "") ||
                  closingReference(issue).test(pr.body ?? ""))
            : pr?.state === "MERGED" &&
              pr.baseRefName === "main" &&
              closingReference(issue).test(pr.body ?? "");
    if (!valid)
        fail(`${from} → ${from === "Active" ? "Review" : "Verify"} PR evidence is invalid.`);
    return `PR #${number}`;
}

export async function runDeliveryStatus(argv, { gh = nativeGh, write = console.log } = {}) {
    const options = parse(argv);
    if (transitions.get(options.from) !== options.to)
        fail(`Unsupported transition: ${options.from} → ${options.to}. Blocked is manual.`);
    const issue = await json(gh, [
        "issue",
        "view",
        options.issue,
        "--repo",
        repo,
        "--json",
        "id,number,labels",
    ]);
    if (
        !issue?.id ||
        issue.number !== Number(options.issue) ||
        !issue.labels?.some((label) => label.name === "status:approved")
    )
        fail(`Issue #${options.issue} must exist and carry status:approved.`);

    const [[projectData, status], projectItems] = await Promise.all([board(gh), items(gh)]);
    const source = status.options.filter((option) => option.name === options.from);
    const target = status.options.filter((option) => option.name === options.to);
    const item = matching(projectItems, issue);
    if (source.length !== 1 || target.length !== 1 || !target[0].id)
        fail("Requested Status value is unavailable.");
    if (item.length !== 1 || !item[0].id || !item[0].status)
        fail(`Issue #${issue.number} must have exactly one Project #2 item.`);
    if (item[0].status !== options.from)
        fail(`Current Project status is ${item[0].status}, not ${options.from}.`);
    if (
        options.to === "Active" &&
        projectItems.some((other) => other.id !== item[0].id && other.status === "Active")
    )
        fail("Active WIP limit reached.");

    const transition = `${options.from} → ${options.to}`;
    const gated = ["Ready", "Active", "Done"].includes(options.to);
    const confirmation =
        options.to === "Done"
            ? "Human confirmation will be required before applying Done: --confirm-human-gate attests integrated acceptance evidence passed."
            : `Human confirmation will be required before applying ${options.to}: --confirm-human-gate.`;
    const evidence = ["approved issue", "unique Project item"];
    if (options.to === "Active") evidence.push("WIP=1");
    if (options.to === "Review" || options.to === "Verify")
        evidence.push(await checkPr(gh, options.from, options.pr, issue.number));
    if (options.apply && gated && !options.confirm) fail(confirmation.replace("will be ", ""));
    write(
        `Mode: ${options.apply ? "apply" : "dry-run"}\nIssue: #${issue.number}\nTransition: ${transition}\nEvidence: ${evidence.join("; ")}`,
    );
    if (!options.apply) {
        if (gated) write(confirmation);
        return { mode: "dry-run" };
    }

    await json(gh, [
        "project",
        "item-edit",
        "--project-id",
        projectData.id,
        "--id",
        item[0].id,
        "--field-id",
        status.id,
        "--format",
        "json",
        "--single-select-option-id",
        target[0].id,
    ]);
    const readback = matching(await items(gh), issue);
    if (readback.length !== 1 || readback[0].status !== options.to)
        fail(`Project readback did not reach ${options.to}.`);
    write(`Readback: ${options.to}`);
    return { mode: "apply" };
}

function startOptions(argv) {
    const result = {};
    for (let index = 0; index < argv.length; index += 1) {
        const argument = argv[index];
        if (argument === "--apply") result.apply = true;
        else if (argument === "--confirm-human-gate") result.confirm = true;
        else if (["--item", "--wave"].includes(argument)) result[argument.slice(2)] = argv[++index];
        else fail(`Unknown argument: ${argument}`);
    }
    if (!result.item || !result.wave) fail("Use --item <PROJECT_ITEM_ID> --wave <Wave N>.");
    return result;
}

function field(fields, name) {
    const matches = fields.filter(
        (candidate) => candidate?.name === name && candidate.type === "ProjectV2SingleSelectField",
    );
    if (matches.length !== 1 || !matches[0].id || !Array.isArray(matches[0].options))
        fail(`Project #2 ${name} is missing or ambiguous.`);
    return matches[0];
}

function option(current, name) {
    const matches = current.options.filter((candidate) => candidate?.name === name && candidate.id);
    if (matches.length !== 1) fail(`Project #2 ${current.name} value is unavailable or ambiguous.`);
    return matches[0];
}

function waveValue(item) {
    if (typeof item?.wave !== "string") fail("Selected item Wave is missing or ambiguous.");
    return item.wave;
}

function draft(item, wave) {
    const content = item?.content;
    if (!item?.id || content?.type !== "DraftIssue") fail("Selected Project item must be a DraftIssue.");
    if (typeof content.title !== "string" || typeof content.body !== "string")
        fail("Selected DraftIssue title or body is malformed.");
    if (item.status !== "Backlog") fail("Selected Project item must be Backlog.");
    if (waveValue(item) !== wave) fail(`Selected Project item is not in ${wave}.`);
    return item;
}

async function startState(gh) {
    const [[projectData, status, fields], projectItems, repository] = await Promise.all([
        board(gh),
        items(gh),
        json(gh, ["repo", "view", repo, "--json", "id,nameWithOwner"]),
    ]);
    if (!repository?.id || repository.nameWithOwner !== repo) fail("Repository identity is unavailable.");
    const wave = field(fields, "Wave");
    return { projectData, status, wave, projectItems, repository };
}

function snapshot(state, selected) {
    const compactField = (current) => ({
        id: current.id,
        options: current.options.map(({ id, name }) => ({ id, name })),
    });
    return JSON.stringify({
        repository: { id: state.repository.id, nameWithOwner: state.repository.nameWithOwner },
        project: state.projectData.id,
        fields: { status: compactField(state.status), wave: compactField(state.wave) },
        item: {
            id: selected.id,
            title: selected.content.title,
            body: selected.content.body,
            type: selected.content.type,
            status: selected.status,
            wave: waveValue(selected),
        },
        active: state.projectItems.filter((item) => item.status === "Active").map((item) => item.id).sort(),
    });
}

function selectedState(state, options) {
    option(state.status, "Backlog");
    option(state.status, "Ready");
    option(state.status, "Active");
    option(state.wave, options.wave);
    const matches = state.projectItems.filter((item) => item?.id === options.item);
    if (matches.length !== 1) fail("Selected Project item is missing or ambiguous.");
    const selected = draft(matches[0], options.wave);
    if (state.projectItems.some((item) => item.status === "Active")) fail("Active WIP limit reached.");
    return selected;
}

async function convert(gh, selected, repositoryId) {
    const query =
        "mutation($itemId:ID!,$repositoryId:ID!){convertProjectV2DraftIssueItemToIssue(input:{itemId:$itemId,repositoryId:$repositoryId}){item{id}}}";
    let data;
    try {
        data = JSON.parse(
            await gh(["api", "graphql", "-f", `query=${query}`, "-F", `itemId=${selected.id}`, "-F", `repositoryId=${repositoryId}`]),
        );
    } catch {
        fail(`Conversion outcome is indeterminate; inspect Project item ${selected.id} before retrying.`);
    }
    if (data?.data?.convertProjectV2DraftIssueItemToIssue?.item?.id !== selected.id)
        fail(`Conversion response is malformed; inspect Project item ${selected.id} before retrying.`);
}

function converted(itemsAfter, selected, wave, status = "Backlog") {
    const matches = itemsAfter.filter((item) => item?.id === selected.id);
    const item = matches[0];
    if (
        matches.length !== 1 ||
        item?.content?.type !== "Issue" ||
        item.content.repository !== repo ||
        !Number.isInteger(item.content.number) ||
        item.content.number < 1 ||
        item.content.title !== selected.content.title ||
        item.status !== status ||
        waveValue(item) !== wave
    )
        fail("Converted Project item readback is malformed.");
    return item;
}

async function known(stage, number, action) {
    try {
        return await action();
    } catch (error) {
        fail(`Start-next stopped after ${stage}${number ? ` for issue #${number}` : ""}: ${error.message}`);
    }
}

async function moveStartItem(gh, state, itemId, target) {
    await json(gh, [
        "project",
        "item-edit",
        "--project-id",
        state.projectData.id,
        "--id",
        itemId,
        "--field-id",
        state.status.id,
        "--format",
        "json",
        "--single-select-option-id",
        option(state.status, target).id,
    ]);
}

export async function runDeliveryStartNext(argv, { gh = nativeGh, write = console.log } = {}) {
    const options = startOptions(argv);
    const initial = await startState(gh);
    const selected = selectedState(initial, options);
    const frozen = snapshot(initial, selected);
    write(
        `Mode: ${options.apply ? "apply" : "dry-run"}\nItem: ${selected.id} (${selected.content.title})\nWave: ${options.wave}\nPlan: Backlog → Ready → Active\nChecks: DraftIssue; Backlog; Wave; WIP=0\nConversion: creates the durable repository issue without creating a duplicate.`,
    );
    if (!options.apply) return { mode: "dry-run" };
    if (!options.confirm) fail("Human confirmation is required: --confirm-human-gate.");

    const fresh = await startState(gh);
    const current = selectedState(fresh, options);
    if (snapshot(fresh, current) !== frozen) fail("Start-next precondition is stale; no mutation was made.");
    await convert(gh, current, fresh.repository.id);
    const issue = await known("conversion", 0, async () => converted(await items(gh), current, options.wave));
    const number = issue.content.number;
    const issueReadback = await known("conversion", number, async () => {
        await gh(["issue", "edit", number, "--repo", repo, "--add-label", "status:approved"]);
        const data = await json(gh, ["issue", "view", number, "--repo", repo, "--json", "number,url,labels"]);
        if (data?.number !== number || !data?.url || !data.labels?.some((label) => label.name === "status:approved"))
            fail("Issue label readback is malformed.");
        return data;
    });
    await known("label", number, async () => {
        await moveStartItem(gh, fresh, issue.id, "Ready");
        if (converted(await items(gh), current, options.wave, "Ready").status !== "Ready")
            fail("Ready readback is malformed.");
    });
    const active = await known("Ready", number, async () => {
        await moveStartItem(gh, fresh, issue.id, "Active");
        const readback = converted(await items(gh), current, options.wave, "Active");
        if (readback.status !== "Active") fail("Active readback is malformed.");
        return readback;
    });
    write(`Issue: #${number} ${issueReadback.url}\nItem: ${active.id}\nReadback: Active\nHandoff: issue, branch, commit, and PR remain separately authorized.`);
    return { mode: "apply", number, url: issueReadback.url, itemId: active.id };
}

export async function main(argv = process.argv.slice(2)) {
    try {
        if (argv[0] === "start-next") await runDeliveryStartNext(argv.slice(1));
        else if (argv[0] === "pr-status") await runDeliveryPrStatus(argv.slice(1));
        else await runDeliveryStatus(argv);
    } catch (error) {
        console.error(`Delivery status failed: ${error.message}`);
        process.exitCode = 1;
    }
}

if (import.meta.url === new URL(process.argv[1], "file:").href) await main();
