---
name: laravel-maintainable-implementation
description: "Trigger: Laravel maintainable implementation, Laravel Actions, project-owned PHP or Livewire changes. Apply the DeTuristaAndo Laravel implementation authorities."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.4"
---

## Activation Contract

Use for project-owned Laravel, PHP, or Livewire implementation. Do not use for documentation-only work, source migration, or unapproved scaffolding. Read the active work item first; lazy-load references only when the current scope, risk, or ambiguity needs them.

## Hard Rules

- Use conventional Laravel technical roots; never create `app/Modules`, formal layers, or empty capability folders.
- Classify every operation. A meaningful command must use one `<Verb><Subject>Action::handle()`.
- Give each applicable authorization or transaction boundary complete Action ownership; collaborators must not fragment either boundary. Dispatch provider work after commit when local state is authoritative.
- Prefer Eloquent. Add a focused Service, optional abstraction, or contract only for a documented current responsibility or boundary. Keep SDK behavior in project-owned Integrations.
- Follow the canonical standard's complete prospective English PHPDoc/comment contract only when in-scope code needs the full rule; do not eagerly load or reproduce its inventory.
- Generate elegant code: simple, readable, structurally clear, and maintainable before it is clever.
- Do not install Laravel Actions or Boost, add ports, mandatory repositories/interfaces, `AGENTS.md`, `CLAUDE.md`, or source migration.

## Elegant Code Rule

Structure functions as a top-to-bottom narrative. Group related statements into clear semantic blocks, separated by whitespace when useful. Keep each block focused on one responsibility and avoid interleaving unrelated concerns. Extract helper methods only when they improve readability, reuse, or abstraction.

Use concise PHP when it improves clarity: ternaries, null coalescing, collection operations, early returns, and fluent Laravel APIs are welcome when they make the intent easier to read. Reject clever compression, hidden side effects, generic indirection, and long methods explained only by comments.

Prefer modern readable syntax when it makes structure visible. Use heredoc or nowdoc for multi-line HTML, SQL, text, or templates instead of compressed one-line strings; keep the source indentation easy to read. Interpolate and escape only when data is dynamic or untrusted; do not add escaping, helpers, variables, or collection pipelines for static literals.

Inside a function, add an implementation comment only when the block needs it to become faster to understand. Prefer better naming and structure over comments that narrate obvious code. Extract helpers only when they remove meaningful complexity; do not split tiny static markup or simple literals into indirection.

## Decision Gates

| Situation | Action |
| --- | --- |
| Meaningful command | Use an Action and apply only required boundaries. |
| Routine query, write, or callback | Use the clearest Laravel-native mechanism directly. |
| Livewire or security risk | Keep presentation focused; apply validation, server authorization, integrity, and focused tests. |
| Provider effect | Use an Integration and dispatch it after commit. |

## Execution Steps

1. Load the active item. For persistence or schema work, lazy-load the database standard before design. Choose the smallest clear Laravel-native design from the item itself when possible.
2. Classify the operation, record applicable boundaries, and justify every optional abstraction.
3. Implement focused Laravel-native behavior as a readable top-to-bottom narrative with semantic blocks.
4. Apply security controls and the smallest useful tests for the changed behavior.
5. Apply the prospective documentation contract only to in-scope changed code; lazy-load the canonical standard when the full PHPDoc/comment rule is needed.

## Output Contract

Report changed files, command classification, Action and transaction owner, post-commit effects, abstraction justifications, security/testing and documentation applicability, exclusions, commands run, risks, and `skill_resolution`.

## Lazy References

Load only the reference needed for the current decision:

- [Laravel application standard](../../docs/development/laravel-application-standard.md): full classification, PHPDoc/comment, testing, or abstraction rule.
- [Database standard](../../docs/development/database-standard.md): PostgreSQL persistence, schema, migration, relationship, lifecycle, audit, or indexing decision.
- [ADR-007](../../docs/architecture/decisions/007-conventional-laravel-monolith-with-use-case-actions.md): architecture authority or Action convention ambiguity.
- [ADR-008](../../docs/architecture/decisions/008-google-wallet-project-owned-integration.md): Google Wallet or provider-boundary work.
- [Architecture overview](../../docs/architecture/overview.md): broad placement or cross-cutting architecture ambiguity.
- [Development workflow](../../docs/development/workflow.md): delivery, branch, PR, or Kanban questions.
