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
const reference = (issue, closing) =>
    new RegExp(
        `\\b(?:${closing ? "closes|fixes|resolves" : "closes|fixes|resolves|refs|references|part of"})\\s*:?\\s*#${issue}\\b`,
        "i",
    );

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
    return [data, status[0]];
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
            ? pr?.state === "OPEN" && reference(issue, false).test(pr.body ?? "")
            : pr?.state === "MERGED" &&
              pr.baseRefName === "main" &&
              reference(issue, true).test(pr.body ?? "");
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

export async function main(argv = process.argv.slice(2)) {
    try {
        await runDeliveryStatus(argv);
    } catch (error) {
        console.error(`Delivery status failed: ${error.message}`);
        process.exitCode = 1;
    }
}

if (import.meta.url === new URL(process.argv[1], "file:").href) await main();
