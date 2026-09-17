# DeTuristaAndo Database Standard

## Answer first

This document is the operational authority for project-owned PostgreSQL 16 schema work and Laravel migrations. PostgreSQL `public` is the authoritative application schema. Laravel migrations are the authoritative schema history.

Every rule in this standard names its scope. Every new project-owned application table follows the same applicable baseline. Existing Laravel, framework, and vendor tables are exempt unless project code materially replaces their schema.

Use conventional Laravel and PostgreSQL features. Model only current domain needs. Keep database rules proportional to demonstrated product risk and access paths.

## Quick path

1. Read the active item, this standard, and the linked domain and security authorities.
2. Model the smallest current record set in PostgreSQL `public` with Laravel migrations.
3. Use the naming, identifier, type, and invariant decisions below.
4. Declare each foreign-key action and add only evidence-based indexes.
5. Add factories and focused persistence tests. Add local demo seeders only when a developer opts in.
6. Record unresolved retention, deletion, audit, and idempotency decisions before the affected tables ship.

## Scope and authority

| Area | Rule |
|---|---|
| Database | PostgreSQL 16 is the authoritative application database. |
| Schema | Use `public`. Do not introduce cross-schema functional coupling. |
| History | Laravel migrations define application schema history. Make each migration reversible or state explicit recovery steps. |
| Access | Use Eloquent first. Use the query builder when a database-specific or aggregate query is clearer. |
| Seeds | Use factories for tests. Use seeders for opt-in local demos. Never create demo rows automatically in production. |

## Naming and identifiers

| Element | Required rule |
|---|---|
| Table | Plural `lower_snake_case`, such as `experiences`. |
| Column | Singular `lower_snake_case`, such as `published_at`. |
| Foreign key | `<singular>_id`, such as `experience_id`. |
| Internal key | `bigint id` primary key. |
| Public key | Add opaque `public_id` where a record is exposed outside trusted internal boundaries. Use PostgreSQL `uuid` with an application-generated UUIDv7. |
| Public API and UI | Never expose sequential internal IDs. |
| Constraint and index name | Accept Laravel conventional names. Use an explicit shorter name only when necessary. |

## Data and invariant decisions

| Need | Required choice |
|---|---|
| Required value | Use `NOT NULL`. |
| Unique business value | Explicitly choose all-row or non-deleted-row uniqueness based on domain semantics. Use `UNIQUE` for all-row uniqueness. |
| Valid range or relationship inside a row | Use `CHECK`. |
| Referenced record | Use a foreign key, except for the historical polymorphic actor references below. |
| Missing or implied value | Declare nullability and defaults explicitly. Do not use sentinel values. |
| Absolute moment | Use a timezone-aware timestamp. Mutable domain entities use the common lifecycle timestamps below. |
| Calendar-only value | Use `date`. |
| Money | Store minor units in an appropriate `integer`, `bigint`, or `numeric` column. Do not use floating point. |
| Variable metadata or provider payload boundary | Use `jsonb` only when allowlisted and variable by design. |
| Business lifecycle | Use canonical `status`, a PHP backed enum, and a database `CHECK` for allowed values. Do not use native database enums by default. |

Database constraints enforce database invariants. Application validation improves feedback but does not replace a constraint.

## Uniform table baseline

**Scope: project-owned mutable domain entities**, including `experiences` and `participants`. Use common timestamps and last-actor metadata rather than custom per-table variants. Immutable facts and audit rows are append-only in ordinary operation; they do not inherit a blanket update/delete metadata requirement. Framework, Starter Kit, `users`, and vendor schemas and account behavior are exempt and unchanged; ordinary User hard deletion remains supported.

Mutable domain entities must carry these columns:

- `created_at`, `created_by_type`, `created_by_id`
- `updated_at`, `updated_by_type`, `updated_by_id`
- `deleted_at`, `deleted_by_type`, `deleted_by_id`

Use `timestampsTz()` for `created_at` and `updated_at`, and `softDeletesTz()` for `deleted_at`. Actor types are strings; actor IDs are nullable `VARCHAR` holding native scalar identifiers as text, not public resource identities.

| Actor type | ID rule |
|---|---|
| `user` | Required canonical decimal text of the Starter Kit User `id`. |
| `system` | `null`; the caller deliberately supplies system context, including seeders and migrations. |

Use these two actor types. Introduce non-user principals only for an actual requirement-defined use case, not invented identifiers, an actor registry, or resolver scaffolding.

`created_by_type` is required for new rows. Updated/deleted actor pairs remain null until their event occurs. Polymorphic historical actor references have **no foreign key**: deleting a User must not cascade, restrict deletion, or erase attribution. Retained identifiers do not guarantee resolvable personal history after account deletion. Actor pairs are internal and must never enter public identity or API serialization; domain resource `public_id` rules remain separate.

Actor type conversions must preserve existing identifier values and attribution without loss or silent reassignment to another principal. Rollback must reject identifiers incompatible with the destination type; provide a guarded migration and an explicit recovery procedure where reversal is unsafe.

### Attribution ownership

Owning write Actions must set attribution server-side, never from untrusted form mass assignment or a generic observer fallback. Missing actor context must not silently become `system`. Creation, update, and soft deletion record their applicable actor; restoration atomically clears `deleted_at` and both deleted-actor fields and sets the updated actor to the restorer.

These fields retain the creator and latest updater/deleter, not a sequence of changes. Soft deletion preserves the latest row, not versions or exact past attributes. Semantic audit belongs to its sensitive command, not to generic row-change history.

## Relationships, deletion, and indexes

| Decision | Required rule |
|---|---|
| Foreign-key action | **Scope: every project-owned foreign key.** Declare update and deletion behavior explicitly. Use `RESTRICT` by default. |
| `CASCADE` | **Scope: declared hard-delete recovery paths only.** Use only for exclusive, disposable children or pivot rows. |
| `SET NULL` | **Scope: optional retained evidence.** Use only for optional retained evidence. |
| Immutable or audit history | **Scope: immutable facts and audit rows.** Never cascade delete it. |
| Hard deletion | **Scope: project-owned mutable domain entities.** Use soft deletion for ordinary removal. Hard deletion requires documented retention/purge or rollback/recovery; exempt Starter Kit User account deletion remains unchanged. |
| Operational query | **Scope: ordinary application reads.** Exclude soft-deleted rows by default. |
| Analytics or administrative query | **Scope: authorized historical reporting.** Include retained records according to event period and meaningful business facts, not only currently active or non-deleted parents. |
| Unique business value | **Scope: every uniqueness rule.** State whether uniqueness covers all rows or only non-deleted rows. For active-row reuse, use a PostgreSQL partial unique index over non-deleted rows. |
| Referencing-column index | **Scope: demonstrated joins or deletes.** Add one when needed. PostgreSQL does not create it automatically for a foreign key. |
| Composite or partial index | **Scope: demonstrated constraint or access path.** Add only when needed. |
| Speculative index | **Scope: all project-owned tables.** Do not add it. |

Referential actions encode record ownership and retention. Choose them from the domain lifecycle, not from migration convenience.

Public child reads must respect parent visibility. Soft-deleting a parent does not soft-delete its children; restoring it does not revive independently deleted children. Historical reportability is distinct from public visibility and does not provide exact past attribute reconstruction. Reporting implementation belongs to its owning feature.

## Lifecycle, audit, and commands

- **Scope: domain entities with an actual business lifecycle.** Use `status` with a PHP backed enum and database `CHECK`. Experience values are `draft`, `published`, and `cancelled`; schedule-derived `upcoming`, `active`, and `finished` are not stored status values.
- **Scope: entities without a lifecycle, pure facts, audit rows, and technical rows.** Do not invent meaningless active/inactive states. No separate business status is defined for Participant; `deleted_at` represents logical removal.
- **Scope: project-owned mutable domain entities.** Carry the common timestamp and actor baseline above.
- **Scope: immutable domain behavior.** Model visits and redemptions as immutable facts.
- **Scope: the first sensitive command that owns semantic audit.** Introduce `audit_events` only when a sensitive command requires it. The owning Action writes it transactionally. Facts and audit events remain append-only in ordinary operation: preserve events and define explicit invalidation/correction semantics in the owning feature.
- **Scope: all work before a sensitive command owns it.** Do not add generic row-change triggers, `_hist` tables, or audit/history scaffolding.
- **Scope: audit event payloads.** Allowlist and sanitize them. Record the applicable when, where, who, and what. Exclude or protect tokens, passwords, connection strings, keys, and sensitive PII.
- **Scope: consequential database commands.** One Action owns each transaction. Run external effects after commit.
- **Scope: sensitive commands.** Use durable scoped idempotency and locking or uniqueness as their risk requires.

Controlled retention/purge is separate from ordinary deletion and event correction. Retention periods, domain organizer deletion behavior, audit enforcement, and exact idempotency receipts remain explicit human decisions to resolve before affected production data ships. Preservation is not permission for indefinite PII retention.

## Publication and temporal state

Business publication `status` is separate from date-derived `upcoming`, `active`, and `finished` phases. A date must not silently publish or unpublish an experience. Add write Actions, audit, retention, and idempotency persistence only when an owning feature requires them.

## Migration review checklist

- [ ] The migration changes only PostgreSQL `public` application persistence.
- [ ] Tables, columns, foreign keys, and names follow the naming rules.
- [ ] Internal and public identifiers have separate purposes.
- [ ] Nullability, defaults, types, and database invariants are explicit.
- [ ] Mutable domain entities use common timezone-aware timestamps, soft deletion, and `created_by`, `updated_by`, and `deleted_by` type/ID pairs; immutable facts and exempt schemas are not forced into this baseline.
- [ ] Internal actor IDs are nullable `VARCHAR`: new writes use canonical decimal User IDs or deliberate `system` with null ID, without foreign keys or public serialization.
- [ ] Type conversions preserve existing attribution; rollback rejects incompatible identifiers and unsafe reversal has explicit recovery steps. Owning Actions control attribution and atomic restoration.
- [ ] Every foreign key has explicit update and deletion behavior.
- [ ] Each uniqueness rule declares all-row or non-deleted-row scope; active-row reuse uses a partial unique index.
- [ ] Ordinary reads exclude soft-deleted rows and public children respect parent visibility; historical reports use event periods and business facts without promising past attribute reconstruction.
- [ ] Ordinary domain removal uses soft deletion without cascading it to children; exempt User deletion is unchanged. Domain purge/recovery has an explicit procedure.
- [ ] Each added index has a demonstrated constraint, join, delete, or read path.
- [ ] Lifecycle, audit, transaction, provider, locking, and idempotency behavior matches the current work item.
- [ ] The migration is reversible or has explicit recovery steps.
- [ ] Factories and focused tests exist; demo data remains opt-in and non-production.
- [ ] Required human decisions are resolved before affected tables ship.

## Evidence and limits

| Source | Verified claim used by this standard |
|---|---|
| [PostgreSQL 16: Constraints](https://www.postgresql.org/docs/16/ddl-constraints.html) | PostgreSQL does not automatically create indexes on referencing foreign-key columns. Referential actions define deletion behavior. |
| [PostgreSQL 16: Partial Indexes](https://www.postgresql.org/docs/16/indexes-partial.html) | PostgreSQL partial indexes can enforce uniqueness only for rows matching a predicate. |
| [Laravel 13: Migrations](https://laravel.com/docs/13.x/migrations) | Laravel provides conventional migration support for identifiers, foreign keys, delete actions, `timestampsTz`, and `softDeletesTz`. |
| [OWASP Logging Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Logging_Cheat_Sheet.html) | Security logging needs when, where, who, and what. Tokens, passwords, connection strings, keys, and sensitive PII must be excluded or protected. |

These sources describe platform capabilities and security guidance. They do not decide DeTuristaAndo domain retention, organizer deletion, audit enforcement, or idempotency receipt policy. Those decisions remain human-owned until a scoped work item records them.
