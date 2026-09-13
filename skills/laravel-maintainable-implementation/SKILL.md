---
name: laravel-maintainable-implementation
description: "Trigger: Laravel maintainable implementation, Laravel Actions, project-owned PHP or Livewire changes. Apply the DeTuristaAndo Laravel implementation authorities."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Activation Contract

Use for project-owned Laravel, PHP, or Livewire implementation. Do not use for documentation-only work, source migration, or unapproved scaffolding. Read the active work item and all References before deciding scope.

## Hard Rules

- Use conventional Laravel technical roots; never create `app/Modules`, formal layers, or empty capability folders.
- Classify every operation. A meaningful command must use one `<Verb><Subject>Action::handle()`.
- Give each applicable authorization or transaction boundary complete Action ownership; collaborators must not fragment either boundary. Dispatch provider work after commit when local state is authoritative.
- Prefer Eloquent. Add a focused Service, optional abstraction, or contract only for a documented current responsibility or boundary. Keep SDK behavior in project-owned Integrations.
- Follow the canonical standard's complete prospective English PHPDoc/comment contract; do not reproduce its inventory here.
- Do not install Laravel Actions or Boost, add ports, mandatory repositories/interfaces, `AGENTS.md`, `CLAUDE.md`, or source migration.

## Decision Gates

| Situation | Action |
| --- | --- |
| Meaningful command | Use an Action and apply only required boundaries. |
| Routine query, write, or callback | Use the clearest Laravel-native mechanism directly. |
| Livewire or security risk | Keep presentation focused; apply validation, server authorization, integrity, and focused tests. |
| Provider effect | Use an Integration and dispatch it after commit. |

## Execution Steps

1. Load the active item and References; choose the smallest clear design.
2. Classify the operation, record applicable boundaries, and justify every optional abstraction.
3. Implement focused Laravel-native behavior, security controls, and applicable tests.
4. Apply the prospective documentation contract only to in-scope changed code.

## Output Contract

Report changed files, command classification, Action and transaction owner, post-commit effects, abstraction justifications, security/testing and documentation applicability, exclusions, commands run, risks, and `skill_resolution`.

## References

- [Laravel application standard](../../docs/development/laravel-application-standard.md)
- [ADR-007](../../docs/architecture/decisions/007-conventional-laravel-monolith-with-use-case-actions.md)
- [ADR-008](../../docs/architecture/decisions/008-google-wallet-project-owned-integration.md)
- [Architecture overview](../../docs/architecture/overview.md)
- [Development workflow](../../docs/development/workflow.md)
