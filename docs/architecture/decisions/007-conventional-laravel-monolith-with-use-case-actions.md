# ADR-007: Use a conventional Laravel monolith with use-case Actions

**Date:** 2026-09-08
**Status:** Accepted
**Supersedes:** [ADR-001: Modular monolith with hexagonal architecture](001-modular-monolith-and-hexagonal-architecture.md)

## Context

The current architecture authorities prescribe formal layers, modules, ports, and adapters. That structure conflicts with the selected Laravel-native model and adds ceremony before real product responsibilities exist. ADR-001 remains an unchanged accepted historical record, but this ADR is the current application-architecture decision.

Laravel provides conventional technical roots and permits application-specific organization; it does not prescribe one universal Actions, Services, repositories, module, or layer structure. The project needs a clear convention for consequential commands without turning every operation into a pattern chain.

## Options considered

1. Retain ADR-001 and its formal modular-hexagonal structure.
2. Use conventional Laravel with no shared convention for consequential commands.
3. Use a conventional Laravel monolith with focused use-case Actions.

## Decision

Use one conventional Laravel monolith. Put real project code in conventional Laravel technical roots, including `app/Actions`, `app/Models`, `app/Livewire`, `app/Http`, `app/Policies`, `app/Services`, `app/Integrations`, `app/Jobs`, `app/Events`, `app/Enums`, and `app/Data`. Group code by product capability within a technical root only when real cohesive code benefits from it.

After classifying an operation as a meaningful business command, implement or coordinate it in one project-owned `<Verb><Subject>Action::handle()`. A meaningful command has an imperative product outcome, changes authoritative state or records a consequential decision, and coordinates a non-routine concern such as a consequential rule, authorization, atomic work, concurrency or idempotency, or an effect tied to that state change.

An Action may coordinate authorization, its complete database transaction, Eloquent, focused Services, and post-commit dispatch. When local state is authoritative, commit it before dispatching dependent provider work. Use Eloquent and, when clearer, the query builder by default. Models may own relationships, casts, scopes, and cohesive local behavior. Keep provider SDK behavior in project-owned Integrations.

Use a focused Service only for a reusable cohesive capability or composed read. Add a repository, interface, contract, DTO, enum, value object, Service, query, or capability directory only for a documented current responsibility or boundary. A generic CRUD bucket, symmetry, anticipated reuse, or a future possibility is not sufficient justification.

## Decision limits

Use-case Actions are a DeTuristaAndo convention. They are not an official Laravel standard or an industry-wide architecture standard.

Do not create formal modules, `app/Modules`, Domain/Application/Infrastructure scaffolding, or empty capability folders. Do not require ports, adapters, repositories, interfaces, contracts, or an Action-to-Service-to-Repository chain. Do not install the Laravel Actions package. Routine writes, queries, framework callbacks, and mechanical wrappers do not require an Action.

## Consequences

### Positive

- Laravel conventions and direct Eloquent use reduce structural ceremony.
- Classified commands have one clear coordination and transaction owner.
- Capability ownership and project-owned Integrations make product and provider responsibilities visible.
- Current-need abstractions preserve KISS, YAGNI, DRY, and SOLID without a mandatory taxonomy.

### Negative

- The project accepts Laravel coupling where it is the clearest fit.
- Review discipline must prevent oversized Actions and generic Services.
- A proposed abstraction needs documented evidence of its current responsibility or boundary.

## Related requirements

- `REQ-MNT-001` through `REQ-MNT-004`.
- `REQ-REL-001`.
- `REQ-SEC-001` through `REQ-SEC-005`.

## References

- [Laravel 13.x directory structure](https://laravel.com/docs/13.x/structure) — primary framework structure evidence.
- [Laravel 13.x Eloquent ORM](https://laravel.com/docs/13.x/eloquent) — primary persistence evidence.
- [Laravel Beyond CRUD](https://stitcher.io/blog/laravel-beyond-crud) — supporting community discussion of application organization, not a universal architecture authority.
