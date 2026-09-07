#!/usr/bin/env node

import { existsSync, readFileSync, readdirSync, statSync } from "node:fs";
import { dirname, relative, resolve, sep } from "node:path";
import { pathToFileURL } from "node:url";

const requirementPattern = /^REQ-[A-Z]+-\d{3}$/;
const requirementCandidatePattern = /\bREQ-[A-Z\d-]+\b/g;
const ignoredDirectories = new Set([".git", "node_modules", "storage", "vendor"]);

function markdownFiles(root) {
    const files = [];
    const roots = ["README.md", "docs"];

    function visit(path) {
        if (!existsSync(path)) return;

        const stats = statSync(path);
        if (stats.isFile()) {
            if (path.endsWith(".md")) files.push(path);
            return;
        }

        for (const entry of readdirSync(path, { withFileTypes: true })) {
            if (entry.isDirectory() && ignoredDirectories.has(entry.name)) continue;
            visit(resolve(path, entry.name));
        }
    }

    for (const path of roots) visit(resolve(root, path));

    return files.sort();
}

function lineNumber(content, offset) {
    return content.slice(0, offset).split("\n").length;
}

function markdownTargets(content) {
    const targets = [];
    const inlinePattern = /!?\[[^\]]*\]\(([^)\s]+)(?:\s+["'][^"']*["'])?\)/g;
    const referencePattern = /^\s*\[[^\]]+\]:\s+(\S+)/gm;

    for (const pattern of [inlinePattern, referencePattern]) {
        for (const match of content.matchAll(pattern)) {
            targets.push({ target: match[1], offset: match.index });
        }
    }

    return targets;
}

function localTarget(target) {
    const normalized = target.replace(/^<|>$/g, "");

    if (
        normalized.startsWith("#") ||
        normalized.startsWith("/") ||
        normalized.startsWith("//") ||
        /^[a-z][a-z\d+.-]*:/i.test(normalized)
    ) {
        return null;
    }

    try {
        return decodeURIComponent(normalized.split("#", 1)[0].split("?", 1)[0]);
    } catch {
        return normalized;
    }
}

export function validateMarkdownLinks(root) {
    const errors = [];

    for (const file of markdownFiles(root)) {
        const content = readFileSync(file, "utf8");

        for (const { target, offset } of markdownTargets(content)) {
            const local = localTarget(target);
            if (!local) continue;

            const destination = resolve(dirname(file), local);
            const relativeTarget = relative(root, destination);
            const escapedRoot = relativeTarget === ".." || relativeTarget.startsWith(`..${sep}`);

            if (escapedRoot || !existsSync(destination)) {
                errors.push(
                    `${relative(root, file)}:${lineNumber(content, offset)} references missing path ${target}`,
                );
            }
        }
    }

    return errors;
}

export function validateRequirementIds(root) {
    const errors = [];
    const register = resolve(root, "docs/requirements.md");

    if (!existsSync(register)) return ["docs/requirements.md is missing"];

    const definitions = new Map();
    const registerContent = readFileSync(register, "utf8");
    const definitionPattern = /^####\s+(REQ-[A-Z]+-\d{3})\s+—\s+/gm;

    for (const match of registerContent.matchAll(definitionPattern)) {
        const id = match[1];
        if (definitions.has(id)) {
            errors.push(`docs/requirements.md defines ${id} more than once`);
        }
        definitions.set(id, true);
    }

    if (definitions.size === 0) {
        errors.push("docs/requirements.md contains no canonical requirement headings");
    }

    for (const file of markdownFiles(root)) {
        const content = readFileSync(file, "utf8");

        for (const match of content.matchAll(requirementCandidatePattern)) {
            const id = match[0];
            const location = `${relative(root, file)}:${lineNumber(content, match.index)}`;

            if (!requirementPattern.test(id)) {
                errors.push(`${location} contains malformed requirement identifier ${id}`);
            } else if (!definitions.has(id)) {
                errors.push(`${location} references unknown requirement ${id}`);
            }
        }
    }

    return errors;
}

export function validateRepository(root) {
    return [...validateMarkdownLinks(root), ...validateRequirementIds(root)];
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
    const root = resolve(process.argv[2] ?? process.cwd());
    const errors = validateRepository(root);

    if (errors.length > 0) {
        console.error(errors.map((error) => `- ${error}`).join("\n"));
        process.exitCode = 1;
    } else {
        console.log("Repository links and requirement identifiers are valid.");
    }
}
