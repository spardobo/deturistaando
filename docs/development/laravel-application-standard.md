# Laravel Application Standard

This is the operational authority for future project-owned Laravel, PHP, and Livewire work. It applies prospectively and implements [ADR-007](../architecture/decisions/007-conventional-laravel-monolith-with-use-case-actions.md) and [ADR-008](../architecture/decisions/008-google-wallet-project-owned-integration.md). The [architecture overview](../architecture/overview.md), [requirements](../requirements.md), [security authority](../architecture/security.md), and [workflow](workflow.md) retain their own responsibilities.

For PostgreSQL schema and Laravel migration decisions, use the companion [database standard](database-standard.md).

## Quick path

1. Read the active item and the linked authorities. Choose the smallest clear Laravel-native design.
2. Classify the operation before choosing an Action, Service, query, or abstraction.
3. Put real code in its conventional technical root. Add a capability group only when current code benefits.
4. Make authorization, transaction ownership, provider timing, focused tests, and prospective documentation one behavior.

## Structure and classification

Use real responsibilities in `app/Actions`, `app/Models`, `app/Livewire`, `app/Http`, `app/Policies`, `app/Services`, `app/Integrations`, `app/Jobs`, `app/Events`, `app/Enums`, and `app/Data`. A capability may qualify a root, such as `app/Actions/Participation`, only when real cohesive code exists.

Do not create empty capability directories, `app/Modules`, or Domain/Application/Infrastructure scaffolds. In this standard, application, domain, persistence, and integration are PHPDoc responsibility scopes, not required layers or directories.

Classify an operation as a meaningful business command only when all tests pass:

1. It has an imperative product outcome, such as publish, confirm, redeem, or revoke.
2. It changes authoritative product state or records a consequential product decision.
3. It coordinates a consequential rule, non-mechanical authorization, atomic multi-record work, concurrency or idempotency, or a state-tied event, job, audit, or provider effect.

| Classification | Required design | Examples |
|---|---|---|
| Meaningful command | One project-owned `<Verb><Subject>Action::handle()`. | `PublishExperienceAction::handle()` and `ConfirmVisitAction::handle()`. |
| Routine operation | Use the clearest Laravel mechanism directly. | One-field setting write, simple Eloquent query, or `mount()` callback. |
| Mechanical wrapper | Do not add an Action or Service. | A method that only forwards to a Model or another Service. |

An Action owns command-specific authorization only when the command requires authorization and owns the complete database transaction only when the command requires a transaction. Its callers, Services, Integrations, and other collaborators must not create competing partial authorization or transaction boundaries. Keep network calls outside a required transaction. When local state is authoritative, commit it first and dispatch dependent provider work with after-commit semantics. A failed provider effect does not roll back committed authoritative state.

Actions are a DeTuristaAndo convention, not an official Laravel or universal industry rule. Do not install the Laravel Actions package. Do not use `__invoke()` as a competing Action entry point.

## Services, persistence, and abstractions

| Choice | Use when | Reject when |
|---|---|---|
| Cohesive-capability Service | A reusable, focused capability has a stable current responsibility, for example `QrPayloadSigner`. | A `UserService` or `ExperienceService` generic CRUD bucket. |
| Composed-read Service | A current read composes sources or projections beyond a clear Model or query, for example `OrganizerDashboardService`. | A wrapper around `Model::find()`, one scope, or one simple query. |
| Eloquent / Model | Default persistence. Models may own relationships, casts, scopes, and cohesive local behavior. | A repository added only because a Model exists. |
| Query builder | It makes an aggregate, projection, bulk operation, or database-specific query clearer. | A layer added merely for symmetry. |
| Query, repository, interface/contract, DTO/Data, enum, value object, extra Service, or directory | A work item documents the concrete current responsibility or boundary and why the smaller Laravel-native choice is insufficient. | Generic CRUD, anticipated reuse, future possibility, or a mandatory Action-to-Service chain. |

A Query may name a complex or reused filtering, join, aggregation, pagination, or projection. A DTO/Data object may clarify a meaningful payload boundary. An enum may clarify a closed current set. A value object may enforce a current validation, unit, normalization, equality, or invariant. These choices do not adopt DDD.

The list is not closed. A documented current responsibility or boundary may justify another abstraction. Do not reject it only because it is unlisted. Do not create a formal layer, mandatory port, or a mirrored directory taxonomy.

## Integrations, delivery, security, and tests

Keep provider SDK calls, request and response mapping, configuration interpretation, and provider-error classification in project-owned `app/Integrations/<Capability>` classes or another documented integration location. Name the provider and responsibility, such as `GoogleWalletPassPublisher`; avoid vague `Adapter`, `Manager`, or `Helper` names.

A concrete Integration is the default. Add a capability-named contract only for a documented current substitution, test, or dependency boundary need. Do not require a port. For Google Wallet, preserve ADR-008: PostgreSQL is authoritative, the private web experience remains available, and retryable synchronization follows commit.

- Keep Livewire presentation state, validation feedback, and interaction orchestration. Delegate meaningful commands to Actions.
- Let HTTP handlers, console commands, jobs, and listeners use Laravel conventions and call an Action or focused Service when classification requires it.
- Use Laravel validation, policies or Gate, database constraints, transactions and locks, idempotency keys, events, jobs, queues, Eloquent, and the query builder when each is clearest.
- Authorize on the server at the closest clear Laravel boundary. UI visibility is not authorization. Do not mass-assign unapproved input or expose sequential or private identifiers.
- Test the smallest useful Action/rule, authorization, rejection, transaction rollback, concurrency or idempotency, after-commit dispatch, Livewire orchestration, Laravel fake, or Integration boundary when applicable. Do not add architecture tests or tooling.

## Prospective PHPDoc and comments

This policy applies only to project-owned scoped code created or materially changed after adoption. It does not require cleanup of untouched source, generated or vendor source, tests, migrations, factories, seeders, anonymous classes, or unrelated comments.

Every scoped class needs an English docblock that states its purpose or responsibility and principal guarantee. Every scoped public method needs native types and a complete English PHPDoc contract. Give protected or private methods the same contract only after reasonable refactoring cannot make their complexity, non-obvious invariant, precondition, postcondition, shape or unit, concurrency, idempotency, security, side effect, or exception behavior self-explanatory.

Trivial constructors, obvious accessors, conventional framework hooks, self-explanatory private methods, and closures are exempt unless their contract remains non-self-explanatory. Method length alone never qualifies. Refactor unclear code first; PHPDoc cannot excuse avoidable complexity.

Each required method docblock has an uppercase-first summary, every `@param`, and every `@return`, including `void`. Each tag has useful uppercase-first English prose: parameter meaning and applicable shape or unit, result guarantee, or exception circumstance. Native types do not replace tags.

Use `@throws` only for foreseeable, caller-visible contractual exceptions. List a propagated exception only when it is contractual. Do not inventory engine or dependency `Throwable` values. When no contract exception exists, use the exact prose `Declares no contract exceptions.` and no `@throws` tag. Keep PHPDoc contractual, not an implementation narrative. New or materially changed implementation and configuration comments use lowercase-first English unless grammar requires otherwise.

**Accepted: caller-visible contractual exception.**

```php
/**
 * Publishes an experience for its authorized organizer.
 * Guarantees the experience is committed before provider work dispatches.
 */
final class PublishExperienceAction
{
    /**
     * Publishes the supplied experience for its authorized organizer.
     *
     * @param Experience $experience Experience to publish after readiness validation.
     * @param User $organizer Organizer authorized to publish this experience.
     * @return void Guarantees the committed state is published before dispatch.
     * @throws PublishConflictException When the experience has already been published.
     */
    public function handle(Experience $experience, User $organizer): void {}
}
```

**Accepted: no contractual exception.**

```php
/**
 * Presents public attributes for an experience.
 * Guarantees the returned map exposes only public values.
 */
final class ExperiencePresenter
{
    /**
     * Builds the public experience attributes.
     * Declares no contract exceptions.
     *
     * @param Experience $experience Experience to represent publicly.
     * @return array<string, string> Guarantees the public attribute map.
     */
    public function present(Experience $experience): array
    {
        return [];
    }
}
```

**Rejected: incidental dependency or engine propagation is not a caller contract.** Do not add `@throws JsonException` merely because an internal dependency can propagate it.

```php
final class PayloadEncoder
{
    /**
     * Encodes an internal provider payload.
     * Declares no contract exceptions.
     *
     * @param array<string, mixed> $payload Provider payload to encode.
     * @return string Guarantees the encoded provider payload.
     */
    public function encode(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }
}
```

```php
// queue Wallet synchronization after the transaction commits.

/** Rejected: explains a long private method instead of refactoring it. */
/** Rejected: @param string $code */
/** Rejected: @throws Throwable Any failure. */
```

A qualifying non-public method uses the full contract when, for example, it normalizes a security-sensitive opaque credential or enforces an idempotency invariant that refactoring cannot make clear. A self-explanatory private formatter and a conventional `mount()` hook remain exempt.

## Review checklist

- [ ] The operation passes all three command tests before an Action is required; routine operations remain direct.
- [ ] Does the classified command require authorization? If so, does the Action own it completely?
- [ ] Does the classified command require a database transaction? If so, does the Action own the complete boundary?
- [ ] No caller, Service, Integration, or other collaborator creates a competing partial authorization or transaction boundary; provider work dispatches after commit when local state is authoritative.
- [ ] A Service is a cohesive capability or composed read, not generic CRUD or a required chain.
- [ ] Eloquent or the query builder is the default; every abstraction has a documented current boundary or responsibility.
- [ ] Provider SDK behavior is isolated in a project-owned Integration; a contract has demonstrated current value.
- [ ] Laravel-native validation, authorization, integrity, concurrency, idempotency, and focused tests cover applicable risk.
- [ ] Scoped classes and qualifying methods meet the prospective English PHPDoc and comment contract.

## Evidence and limits

Laravel's [directory structure](https://laravel.com/docs/13.x/structure) and [Eloquent documentation](https://laravel.com/docs/13.x/eloquent) support the selected local convention. [Laravel Beyond CRUD](https://stitcher.io/blog/laravel-beyond-crud) is concise community research. Neither source makes this project's Action convention a universal Laravel architecture claim.
