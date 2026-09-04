# **DeTuristaAndo** Quality Strategy

This document defines the evidence required to release the [MVP01 requirements](requirements.md). Quality work follows product risk and remains small enough for one developer to run continuously.

## Quality priorities

| Priority | Reason | Main evidence |
|---|---|---|
| Domain correctness | Duplicate visits, benefits, or redemptions damage trust. | Domain, integration, and concurrency tests. |
| Authorization | Three access models share one application. | Policy and abuse-case tests. |
| Counter reliability | A failure during live service stops participation. | Browser, retry, and recovery tests. |
| Visitor usability | Registration, slow pages, or unclear QR use causes abandonment. | Mobile E2E, accessibility, and performance evidence. |
| Maintainability | AI-assisted changes can introduce plausible but incorrect code. | Static analysis, focused review, and architecture tests. |

## Test strategy

| Level | Scope | Run |
|---|---|---|
| Unit and domain | `K/N`, repeated visits, entitlement, capacity, expiry, and state transitions. | During development and CI. |
| Feature | Laravel routes, Livewire actions, policies, validation, and audit behavior. | During development and CI. |
| Architecture | Module ownership and inward dependency direction. | CI. |
| Integration | PostgreSQL constraints, transactions, OAuth linking, queues, storage, and provider adapters. | CI with real PostgreSQL and faked external providers. |
| Contract | Project-owned ports and Google Wallet mapping/error classification. | CI; selected provider sandbox checks before release. |
| Browser | Critical visitor, organizer, and business journeys. | Pull request and staging smoke test. |
| Operational | Backup restore, deployment, health, and rollback. | Before production release and after material infrastructure change. |

Use the test pyramid as a planning guide:

| Test share | Initial direction |
|---|---:|
| Unit and domain | `60–70%` |
| Feature, integration, architecture, and contract | `20–30%` |
| Browser and operational | `5–10%` |

The percentages are not release gates. Prefer the cheapest test that proves the behavior. Add browser coverage only when a user journey can fail across layers.

## Test design rules

- Structure focused tests with Arrange, Act, and Assert.
- Test observable behavior instead of private methods or framework implementation.
- Use test-first development for visit, progress, entitlement, redemption, authorization, and concurrency rules.
- Let the developer own the expected behavior. An AI coding agent can propose implementation and tests but cannot define acceptance.
- In Playwright, prefer role, label, and visible text selectors. Use test IDs only when no semantic selector is stable.
- Use Playwright auto-wait. Do not add fixed sleeps to hide timing failures.
- Keep browser helpers or page objects only when a journey repeats stable actions across tests.

## Critical automated scenarios

- Publish only a ready experience with valid `K/N` rules.
- Activate one business invitation once and reject reuse.
- Deny access outside organizer ownership or business scope.
- Create an anonymous participation with separate credentials.
- Confirm the first and a repeated visit to the same participant.
- Reach the goal once under concurrent confirmations.
- Reserve limited benefit capacity without over-allocation.
- Redeem once under repeated or concurrent requests.
- Preserve accepted domain state when Wallet update fails.
- Retry a timed-out validation without duplicate events.
- Complete visitor and business flows by camera and manual code.
- Recover business access after device revocation.

Each core rule needs an accepted case, a relevant rejection case, and concurrency evidence when the rule changes shared state.

## Strategic coverage

Coverage follows a `100/80/0` risk model adapted to the Laravel application.

| Tier | Scope | Initial target |
|---|---|---|
| Core | Visit, distinct progress, entitlement, capacity, redemption, credential scope, and authorization rules. | `100%` of identified rules have direct automated tests. |
| Important | Owned application services, Livewire workflows, and provider adapters. | `80%+` line coverage where measurement is stable and useful. |
| Infrastructure | Framework bootstrap, generated files, configuration-only code, and vendor packages. | No coverage target. |

Do not use one global percentage to justify low-value tests. Review uncovered branches in changed core code. Keep the main branch at `100%` test success with zero accepted flaky tests.

A flaky test is fixed or removed from the required suite. It is never retried until green without investigation.

## Refactoring and code quality

- Refactor behavior without changing its external contract. Separate a risky refactor from unrelated feature work.
- Add characterization tests before changing untested existing behavior.
- Apply the Boy Scout rule only inside the active work-item boundary.
- Treat cognitive complexity above `15` in an owned method as a review signal when the selected analyzer can measure it. Do not add a quality platform only for this number.
- Reject duplicated business knowledge, dead code, pass-through services, generic repositories, and speculative abstractions during review.
- Do not merge a new `TODO` or `FIXME` without an owner or linked work item.

## Local feedback

All executable local quality checks run inside the Sail environment. The host requires Docker, not local PHP, Composer, Node, PostgreSQL, or browser-test runtimes. Use `./vendor/bin/sail` for tests, Pint, Larastan, npm scripts, and Playwright so local and CI evidence share the same runtime boundary.

| Gate | Budget | Checks |
|---|---:|---|
| Pre-commit | Target `≤ 90 s` | Pint, Larastan, and the Unit suite. |
| Pre-push | Target `≤ 3 min` | Frontend production build followed by Pint, Larastan, and all current PHP tests. |

If a gate exceeds its budget consistently, move expensive checks to CI instead of encouraging bypass.

Husky versions both hooks in `.husky/`. The hooks invoke repository-owned Composer scripts through Sail, fail closed when a check fails, and remain convenience gates rather than merge authority. Keep Sail running before committing or pushing. `--no-verify` is reserved for an exceptional recovery; the pull-request CI still repeats every required check independently.

The initial scripts expose `test:unit`, `test:feature`, and `test:all` separately. Coverage runs join pre-push and CI after the first owned domain rules exist. Critical Chromium Playwright runs join those gates after the first complete browser journey exists. Do not introduce placeholder thresholds or browser tests for generated starter-kit code.

Recommended local tools:

- Laravel Pint for PHP style.
- Larastan for static analysis.
- Pest or PHPUnit for PHP behavior.
- Playwright for critical browser journeys and selected visual regression.
- Lighthouse for reproducible public-page performance checks.
- Axe with Playwright for automated accessibility checks.
- A Markdown and relative-link check for documentation.

## Continuous integration

The current GitHub Actions baseline runs on pull requests targeting `main` and on pushes to `main`. CI uses a disposable `ubuntu-latest` runner with PHP 8.4, Composer 2, Node.js 24, and an ephemeral PostgreSQL 16 Alpine service. It installs dependencies exclusively from `composer.lock` and `package-lock.json`, runs `npm ci` and the frontend production build before rendering Feature-test views, and then runs Pint, Larastan, and PHPUnit. Local development continues to use Sail; the hosted CI runner is an isolated verification environment, not a replacement for the local runtime contract.

The current automated gates are:

- Pull-request policy: branch naming, a `Closes`, `Fixes`, or `Resolves #N` reference to at least one `status:approved` issue, and exactly one `type:*` label.
- Weekly Dependabot checks for Composer, npm, and GitHub Actions.

The complete target gate remains:

1. Dependency installation from committed lock files.
2. Laravel Pint and frontend formatting or lint checks.
3. Larastan static analysis.
4. Unit, feature, integration, and architecture tests with PostgreSQL 16.
5. The npm frontend production build.
6. Critical Playwright tests and selected stable visual snapshots in containers.
7. Dependency and secret scans.
8. Production-image build from the root `Dockerfile`, plus a vulnerability scan when deployment files change.
9. Documentation link and identifier checks.

Critical Playwright coverage, stable visual snapshots, container scanning, the GitHub Actions production-image build, and the full documentation checker remain deferred until their dedicated items provide the required automation. The independent production `Dockerfile` is available for local build and smoke evidence, but it is not yet a repository gate. Do not present target gates as current evidence.

Coverage measurement is also deferred until owned domain behavior exists. Its first implementation must exclude generated and infrastructure-only code, report the Core and Important tiers separately where tooling permits, and preserve the `100/80/0` risk interpretation rather than impose one repository-wide percentage.

Use parallel jobs where independence reduces feedback time. The dedicated CI delivery item must verify the independent production build. Build the deployable image once and promote the same artifact.

## Review gate

Protect `main` and merge through a pull request, even for one developer. Required CI replaces unavailable second-person approval; it does not replace deliberate self-review.

Review focuses on:

- Requirement and domain rule changed.
- Authorization and data scope.
- Transaction and idempotency behavior.
- External failure path.
- Tests that prove the outcome.
- Documentation that owns the change.
- Generated code that was accepted without verification.
- Abstraction or pattern use without a current architectural reason.

## Security quality

Apply the minimum gate in the [security architecture](architecture/security.md): authorization and concurrency tests, dependency audit, secret scan, production configuration check, container scan, and focused manual review.

Use OWASP guidance to review applicable risks before public release. Do not add a large security platform when Laravel configuration, tests, and lightweight scanners cover the current risk.

GitHub Copilot Code Review and the Copilot coding agent are not included in Copilot Free and are not configured as repository gates. Reconsider them only if a paid plan or explicit organization entitlement changes that constraint.

## Accessibility and visual quality

Critical flows target WCAG 2.2 AA.

- Run automated accessibility checks on representative public, private, organizer, and business screens.
- Manually test keyboard, focus, zoom, touch, screen-reader labels, reduced motion, and error recovery.
- Verify text contrast of at least `4.5:1` and large text contrast of at least `3:1`.
- Verify that neon color or glow is never the only state signal.
- Test on a mid-range Android device or equivalent profile.
- Keep visual-regression snapshots for representative public landing, private pass, validator review, and completion states. Update a baseline only after deliberate visual review.

Automated accessibility tools find only part of the problem. Manual checks remain required.

## Performance and reliability

| Signal | Initial target |
|---|---|
| Public LCP | `≤ 2.5 s` at p75. |
| Public INP | `≤ 200 ms` at p75. |
| Public CLS | `≤ 0.1` at p75. |
| Validation response | `≤ 2 s` at p95 under the documented baseline load. |
| Direct interaction feedback | Visible response within `100 ms`. |
| Pending operation feedback | Show progress and prevent duplicates after `300 ms`. |
| Database recovery | RPO `24 h`; RTO `8 h`. |

Use lab tests before traffic exists. Add field measurement when the sample is meaningful. Record the device, network, dataset, and concurrency baseline with every result.

Reliability evidence includes:

- idempotent retries;
- queue retry and terminal failure behavior;
- Wallet outage with working private web pass;
- database backup and restore;
- health check and rollback smoke test.

Do not use optimistic UI for visit confirmation, entitlement, capacity, or redemption. Show a pending state until the server returns the authoritative result.

## Observability

Start with structured application logs, release context, health state, queue failures, audit events, and an error tracker such as Sentry if the deployment supports it. Correlation identifiers provide request flow context. Distributed tracing is not required for the initial monolith.

Track:

- unhandled error rate and affected flow;
- validation latency and rejection rate;
- Wallet synchronization failure and retry state;
- time to recover from a production incident.

When client-session monitoring is available, target at least `99.5%` crash-free sessions. Establish the production sample before using this value as a release gate.

Set alert thresholds after a production baseline. Alert immediately on service loss, data integrity risk, or widespread validation failure. Do not alert on every isolated user error.

## Release evidence

A production release requires:

- all required CI jobs green;
- zero unresolved critical defects;
- staging smoke test of the complete visitor and business flow;
- migration and rollback review;
- backup and restore evidence when persistence changed materially;
- required provider credentials and sandbox checks;
- updated living documentation;
- a versioned image and release note.

The release is complete only when the deployed version is healthy and the critical flow works in the target environment.
