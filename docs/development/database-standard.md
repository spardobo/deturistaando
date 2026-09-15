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

## Structural baseline

The project inspected SINAVE DDL as read-only structural evidence. The inspected source had four application schemas and 287 tables. It used lower-snake-case plural tables, conventional `id` and `foo_id` columns, named foreign keys, indexes, and unique constraints. Its dominant traceability columns were `status`, `register_date`, `register_user_id`, `last_update_date`, `last_update_user_id`, and `last_access_control_id`.

The source also had 34 `_hist` snapshot tables with I/U/D markers, an `access_control` audit table, broad native enums, cross-schema foreign keys, no explicit foreign-key actions, one MyISAM legacy table without a primary key, and mixed collations. This evidence informs naming and traceability decisions only. It contains no project data or credentials. A temporary inspected dump is not versioned project evidence.

| SINAVE pattern | Project decision | Reason |
|---|---|---|
| Lower-snake-case plural tables and conventional identifiers | Adopt | Fits Laravel and clear relational naming. |
| Named constraints and indexes | Adapt | Use Laravel conventional names. Specify a shorter explicit name only when PostgreSQL naming limits require it. |
| Universal lifecycle preservation | Adapt deliberately | Use the uniform timestamp and soft-delete baseline for every new project-owned application table. |
| Universal actor/access-control columns and snapshot history | Adapt actor metadata; defer snapshot history | Every new project-owned table records its latest creator, updater, and deleter through the uniform actor baseline. `_hist` tables, triggers, and generic history remain out of scope. |
| Broad native enums | Reject as defaults | They constrain domain evolution without a current domain need. |
| Cross-schema coupling, implicit foreign-key actions, missing primary keys, legacy engine traits, and mixed collations | Reject | They conflict with one authoritative PostgreSQL application schema and explicit integrity. |

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
| Referenced record | Use a foreign key. |
| Missing or implied value | Declare nullability and defaults explicitly. Do not use sentinel values. |
| Absolute moment | Use a timezone-aware timestamp. The mandatory table baseline supplies `created_at`, `updated_at`, and `deleted_at`. |
| Calendar-only value | Use `date`. |
| Money | Store minor units in an appropriate `integer`, `bigint`, or `numeric` column. Do not use floating point. |
| Variable metadata or provider payload boundary | Use `jsonb` only when allowlisted and variable by design. |
| Stable state values | Use a PHP backed enum when it improves application clarity. Do not use PostgreSQL or MySQL native enums by default. |

Database constraints enforce database invariants. Application validation improves feedback but does not replace a constraint.

## Uniform table baseline

**Scope: every new project-owned application table**, including entities, catalogs, pivots, immutable facts, audit tables, and technical project-owned tables. This is a prospective governance baseline; existing Laravel, framework, and vendor tables remain exempt unless project code materially replaces their schema.

Every table carries all of these columns:

- `created_at`, `created_by_type`, `created_by_public_id`
- `updated_at`, `updated_by_type`, `updated_by_public_id`
- `deleted_at`, `deleted_by_type`, `deleted_by_public_id`

Use Laravel's timezone-aware migration methods for the timestamp lifecycle: `timestampsTz()` supplies `created_at` and `updated_at`; `softDeletesTz()` supplies `deleted_at`. Actor public-ID columns use PostgreSQL `uuid`, not sequential internal IDs.

```php
$table->timestampsTz();
$table->string('created_by_type');
$table->uuid('created_by_public_id')->nullable();
$table->string('updated_by_type')->nullable();
$table->uuid('updated_by_public_id')->nullable();
$table->softDeletesTz();
$table->string('deleted_by_type')->nullable();
$table->uuid('deleted_by_public_id')->nullable();
```

Actor metadata records only the latest row-level creator, updater, and deleter. It does not replace semantic `audit_events` history.

| Actor type | Example | Public ID rule |
|---|---|---|
| `organizer_user` | An identified organizer using the application | Required |
| `business_access` | An identified business integration or delegated access principal | Required |
| `anonymous_participation` | A participation flow identified by its stable public identity | Required |
| `platform_operator` | An identified platform support or operations principal | Required |
| `system` | A deliberate system process, including seeders and migrations | May be `null` |
| `anonymous_visitor` | An unauthenticated visitor without a stable public identity | May be `null` |

`created_by_type` is required for every new application-created row. The other actor fields are nullable until their lifecycle event occurs; `created_by_public_id` is also nullable for `system` and `anonymous_visitor`. Identified actor types require a public ID. Seeders and migrations set `created_by_type` to `system` with a `null` public ID. Do not use a database default or implementation fallback that silently assigns `system`: system context must be set deliberately.

Immutable facts and audit rows carry the same columns for schema uniformity. Ordinary application code must not update or soft-delete them. A controlled retention process is their only deletion route.

This standard adds no migration, helper macro, trigger, `_hist` table, `audit_events` table, actor-registry table, or other implementation scaffolding by itself.

## Relationships, deletion, and indexes

| Decision | Required rule |
|---|---|
| Foreign-key action | **Scope: every project-owned foreign key.** Declare update and deletion behavior explicitly. Use `RESTRICT` by default. |
| `CASCADE` | **Scope: declared hard-delete recovery paths only.** Use only for exclusive, disposable children or pivot rows. |
| `SET NULL` | **Scope: optional retained evidence.** Use only for optional retained evidence. |
| Immutable or audit history | **Scope: immutable facts and audit rows.** Never cascade delete it. |
| Hard deletion | **Scope: ordinary application flows.** Forbid it. Permit it only through a documented retention/purge process or rollback/recovery procedure. |
| Operational query | **Scope: ordinary application reads.** Exclude soft-deleted rows by default. |
| Analytics or administrative query | **Scope: authorized analytics and administration.** May include soft-deleted rows explicitly when historical records are required. |
| Unique business value | **Scope: every uniqueness rule.** State whether uniqueness covers all rows or only non-deleted rows. For active-row reuse, use a PostgreSQL partial unique index over non-deleted rows. |
| Referencing-column index | **Scope: demonstrated joins or deletes.** Add one when needed. PostgreSQL does not create it automatically for a foreign key. |
| Composite or partial index | **Scope: demonstrated constraint or access path.** Add only when needed. |
| Speculative index | **Scope: all project-owned tables.** Do not add it. |

Referential actions encode record ownership and retention. Choose them from the domain lifecycle, not from migration convenience.

## Lifecycle, audit, and commands

- **Scope: project-owned domain tables that represent a lifecycle.** Require a `status` value meaningful to that domain. Do not impose generic A/I values where they lose meaning.
- **Scope: pure facts, audit rows, and technical rows.** Do not add meaningless `status` values.
- **Scope: all project-owned application tables.** Carry the uniform actor metadata baseline: `created_by_type` and `created_by_public_id`, `updated_by_type` and `updated_by_public_id`, and `deleted_by_type` and `deleted_by_public_id`. Do not add legacy `last_access_control` columns.
- **Scope: immutable domain behavior.** When visits or redemptions are implemented, model them as immutable facts.
- **Scope: the first sensitive command that owns semantic audit.** Introduce one `audit_events` table only then. The owning application Action writes it transactionally. It follows the uniform timestamp and soft-delete baseline, remains append-only in ordinary operation, and is deletable only by controlled retention.
- **Scope: all work before a sensitive command owns it.** Do not add generic row-change triggers, `_hist` tables, or audit/history scaffolding.
- **Scope: audit event payloads.** Allowlist and sanitize them. Record the applicable when, where, who, and what. Exclude or protect tokens, passwords, connection strings, keys, and sensitive PII.
- **Scope: consequential database commands.** One Action owns each transaction. Run external effects after commit.
- **Scope: sensitive commands.** Use durable scoped idempotency and locking or uniqueness as their risk requires.

The retention period, organizer deletion behavior, audit enforcement and retention, and exact idempotency receipts are explicit human decisions. Decide each before its affected tables ship.

## Wave 2 application boundary

Wave 2 uses PostgreSQL as the authoritative source and adds only minimal real persistence for `experiences` and `participants`.

| Include | Exclude for now |
|---|---|
| Eloquent models, relationships, scopes, and read queries for experiences and participants | Organizer creation and publication scaffolding |
| Editorial publication state | Audit/history/trigger scaffolding; Wave 2 has no sensitive user mutation yet |
| Date-derived `upcoming`, `active`, and `finished` reads | Premature retention or idempotency receipt tables |
| `timestampsTz()`, `softDeletesTz()`, and the uniform actor metadata baseline for experiences and participants | Snapshot history and generic trigger scaffolding |

Editorial publication is separate from date-derived `upcoming`, `active`, and `finished` status. A date must not silently publish or unpublish an experience.

## Migration review checklist

- [ ] The migration changes only PostgreSQL `public` application persistence.
- [ ] Tables, columns, foreign keys, and names follow the naming rules.
- [ ] Internal and public identifiers have separate purposes.
- [ ] Nullability, defaults, types, and database invariants are explicit.
- [ ] Every new project-owned application table has `timestampsTz()`, `softDeletesTz()`, and the complete actor metadata baseline: `created_by_type`/`created_by_public_id`, `updated_by_type`/`updated_by_public_id`, and `deleted_by_type`/`deleted_by_public_id`.
- [ ] Actor public IDs use PostgreSQL `uuid` stable public identities, not internal sequential IDs. `created_by_type` is required for application-created rows; `system` is set deliberately (never as a fallback) and uses a `null` public ID for seeders and migrations.
- [ ] Every foreign key has explicit update and deletion behavior.
- [ ] Each uniqueness rule declares all-row or non-deleted-row scope; active-row reuse uses a partial unique index.
- [ ] Operational query scopes exclude soft-deleted rows by default; authorized analytics/administrative queries may include them explicitly when historical records are required.
- [ ] Ordinary application flows contain no hard delete; each permitted hard delete has a documented retention/purge or rollback/recovery procedure.
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
