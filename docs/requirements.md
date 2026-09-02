# **DeTuristaAndo** Requirements

This document defines the verifiable MVP01 requirements for **DeTuristaAndo**. It specifies product outcomes and acceptance behavior. It does not prescribe fields, endpoints, database tables, or class structure.

## Executive summary

MVP01 contains **69 requirements**: 65 Must and 4 Should. The scope covers the complete product flow from the product home page and public discovery to benefit redemption. It also defines the minimum security, accessibility, performance, reliability, maintainability, observability, and technology constraints needed for a production deployment.

The requirements prioritize the two operations that determine product viability. A visitor must activate and use an anonymous experience pass without an account. A participating business must validate a visit with limited access and without integration with sales, inventory, or payment systems. Google Wallet is part of the initial product. A private web pass protects the flow when Wallet is not available.

The requirements keep implementation detail outside this document. Each requirement has one stable identifier, one MoSCoW priority, one module, one type, one User Story, acceptance criteria in Given-When-Then form, a verification method, and an authoritative source. Detailed field rules and technical tasks belong to the active work item and automated tests.

### Requirement totals

| Measure | Total |
|---|---:|
| Requirements | 69 |
| Must | 65 |
| Should | 4 |
| Could | 0 |
| Won't | 0 requirements; 9 scope exclusions |
| Functional | 43 |
| Security | 5 |
| Usability | 4 |
| Performance | 2 |
| Reliability | 2 |
| Maintainability | 4 |
| Observability | 1 |
| Technical constraint | 8 |

Nine explicit MVP01 exclusions appear at the end of this document. They are scope guards and are not included in the requirement total.

## Requirement register

| # | ID | MoSCoW | Module | Type | Description |
|---:|---|---|---|---|---|
| 1 | REQ-HOM-001 | **Must** | HOM | Functional | Explain the product and direct users to discovery or creation. |
| 2 | REQ-DIS-001 | **Must** | DIS | Functional | Discover active and upcoming experiences with basic filters. |
| 3 | REQ-DIS-002 | **Must** | DIS | Functional | View the complete public information for one experience. |
| 4 | REQ-DIS-003 | **Must** | DIS | Functional | Access and share public content without authentication. |
| 5 | REQ-DIS-004 | **Must** | DIS | Functional | Use a public QR only for discovery and acquisition attribution. |
| 6 | REQ-ORG-001 | **Must** | ORG | Functional | Register, authenticate, and recover organizer access. |
| 7 | REQ-ORG-002 | **Must** | ORG | Functional | Link social and password identities without account takeover. |
| 8 | REQ-ORG-003 | **Must** | ORG | Functional | Manage the lifecycle of an owned experience. |
| 9 | REQ-ORG-004 | **Must** | ORG | Functional | Publish only an experience with a valid required configuration. |
| 10 | REQ-ORG-005 | **Must** | ORG | Functional | Protect structural rules after participation starts. |
| 11 | REQ-ORG-006 | **Must** | ORG | Functional | Deliver and manage participant invitations and readiness. |
| 12 | REQ-ORG-007 | **Must** | ORG | Functional | Manage owned experiences from an organizer workspace. |
| 13 | REQ-EXP-001 | **Must** | EXP | Functional | Configure experience identity, schedule, place, and visual content. |
| 14 | REQ-EXP-002 | **Must** | EXP | Functional | Add and maintain participating businesses with low-friction bulk entry. |
| 15 | REQ-EXP-003 | **Must** | EXP | Functional | Configure the goal, benefit, capacity, validity, and redemption responsibility. |
| 16 | REQ-EXP-004 | **Must** | EXP | Functional | Preview the public landing, Wallet card, and QR material before publication. |
| 17 | REQ-BUS-001 | **Must** | BUS | Functional | Explain invitation identity, scope, and expiry before activation. |
| 18 | REQ-BUS-002 | **Must** | BUS | Functional | Activate limited business access with device context and PIN. |
| 19 | REQ-BUS-003 | **Must** | BUS | Functional | Revoke and scope business access to one participation context. |
| 20 | REQ-BUS-004 | **Must** | BUS | Functional | Operate validation, redemption, public material, and own activity. |
| 21 | REQ-BUS-005 | **Must** | BUS | Functional | Prevent businesses from changing rules or viewing peer data. |
| 22 | REQ-BUS-006 | **Should** | BUS | Functional | Recover a lost or revoked business device. |
| 23 | REQ-PAR-001 | **Must** | PAR | Functional | Activate an anonymous visitor participation. |
| 24 | REQ-PAR-002 | **Must** | PAR | Functional | Reopen and use a private web pass without an account. |
| 25 | REQ-PAR-003 | **Must** | PAR | Functional | Add the participation card to Google Wallet. |
| 26 | REQ-PAR-004 | **Must** | PAR | Functional | Synchronize relevant participation state with Google Wallet. |
| 27 | REQ-PAR-005 | **Must** | PAR | Functional | Preserve participation when Wallet is unavailable. |
| 28 | REQ-PAR-006 | **Must** | PAR | Functional | Separate private-view and validation credentials. |
| 29 | REQ-VIS-001 | **Must** | VIS | Functional | Restrict validation by business scope and visit period. |
| 30 | REQ-VIS-002 | **Must** | VIS | Functional | Review validation context before confirming a visit. |
| 31 | REQ-VIS-003 | **Must** | VIS | Functional | Record an accepted visit as an immutable event. |
| 32 | REQ-VIS-004 | **Must** | VIS | Functional | Count distinct and repeated visits correctly. |
| 33 | REQ-VIS-005 | **Must** | VIS | Functional | Keep progress independent from visit order. |
| 34 | REQ-VIS-006 | **Must** | VIS | Functional | Process validation idempotently with explicit results. |
| 35 | REQ-BEN-001 | **Must** | BEN | Functional | Create one entitlement when the `K/N` goal is reached. |
| 36 | REQ-BEN-002 | **Must** | BEN | Functional | Reserve limited benefit capacity atomically. |
| 37 | REQ-BEN-003 | **Must** | BEN | Functional | Show available, redeemed, and expired benefit states. |
| 38 | REQ-BEN-004 | **Must** | BEN | Functional | Restrict redemption to an authorized point and valid period. |
| 39 | REQ-BEN-005 | **Must** | BEN | Functional | Make redemption final and idempotent. |
| 40 | REQ-ANA-001 | **Must** | ANA | Functional | Show experience measures to the organizer. |
| 41 | REQ-ANA-002 | **Must** | ANA | Functional | Limit business reporting and avoid unsupported impact claims. |
| 42 | REQ-OPS-001 | **Must** | OPS | Functional | Audit sensitive operational actions. |
| 43 | REQ-OPS-002 | **Should** | OPS | Functional | Suspend abusive content or compromised access. |
| 44 | REQ-SEC-001 | **Must** | SEC | Security | Deny protected resources and actions by default. |
| 45 | REQ-SEC-002 | **Must** | SEC | Security | Combine Laravel protections with domain-specific controls. |
| 46 | REQ-SEC-003 | **Must** | SEC | Security | Prevent disclosure of secrets and private credentials. |
| 47 | REQ-SEC-004 | **Must** | SEC | Security | Minimize visitor data and enforce defined retention. |
| 48 | REQ-SEC-005 | **Must** | SEC | Security | Detect common delivery and configuration security failures. |
| 49 | REQ-UX-001 | **Must** | UX | Usability | Meet the accessibility baseline in critical flows. |
| 50 | REQ-UX-002 | **Must** | UX | Usability | Preserve accessible meaning in the dark neon identity. |
| 51 | REQ-UX-003 | **Must** | UX | Usability | Keep public and business flows usable on mobile devices. |
| 52 | REQ-UX-004 | **Must** | UX | Usability | Use domain language and explain unordered participation. |
| 53 | REQ-PER-001 | **Should** | PER | Performance | Meet current Core Web Vitals targets on public pages. |
| 54 | REQ-PER-002 | **Should** | PER | Performance | Complete validation within the initial latency target. |
| 55 | REQ-REL-001 | **Must** | REL | Reliability | Preserve accepted transactions during provider failures. |
| 56 | REQ-REL-002 | **Must** | REL | Reliability | Back up and restore production data within initial objectives. |
| 57 | REQ-MNT-001 | **Must** | MNT | Maintainability | Use a modular monolith with hexagonal architecture. |
| 58 | REQ-MNT-002 | **Must** | MNT | Maintainability | Keep domain rules independent from frameworks and SDKs. |
| 59 | REQ-MNT-003 | **Must** | MNT | Maintainability | Isolate external providers behind narrow ports. |
| 60 | REQ-MNT-004 | **Must** | MNT | Maintainability | Require a current reason for services, repositories, and interfaces. |
| 61 | REQ-OBS-001 | **Must** | OBS | Observability | Emit actionable production and audit information safely. |
| 62 | REQ-TEC-001 | **Must** | TEC | Technical constraint | Use PHP 8.4 and Laravel 13. |
| 63 | REQ-TEC-002 | **Must** | TEC | Technical constraint | Start from the official Laravel Livewire starter kit. |
| 64 | REQ-TEC-003 | **Must** | TEC | Technical constraint | Use Laravel Socialite with Google. |
| 65 | REQ-TEC-004 | **Must** | TEC | Technical constraint | Use the approved presentation stack. |
| 66 | REQ-TEC-005 | **Must** | TEC | Technical constraint | Use PostgreSQL 16 as the authoritative store. |
| 67 | REQ-TEC-006 | **Must** | TEC | Technical constraint | Use Leaflet and OpenStreetMap-compatible map data. |
| 68 | REQ-TEC-007 | **Must** | TEC | Technical constraint | Use the approved test, delivery, and container tools. |
| 69 | REQ-TEC-008 | **Must** | TEC | Technical constraint | Keep artificial intelligence outside product runtime. |

## Normative language and sources

- `MUST` and `MUST NOT` define mandatory behavior.
- `SHOULD` defines expected behavior with a documented exception path.
- MoSCoW priority controls MVP01 delivery order.

| Code | Source |
|---|---|
| RD | [`README.md`](../README.md). |
| MA | [Market analysis](market-analysis.md). |
| CD | [Conceptual design](conceptual-design.md). |
| UX | [UI/UX guidelines](ui-ux-guidelines.md). |
| WS | Product and architecture workshop that approved the MVP01 constraints. |

### Requirement entry structure

Each detailed entry contains metadata, a concise description, one User Story, one or more Given-When-Then scenarios, and a verification method. Add a MoSCoW rationale when the priority is Should or Could, or when a Must priority is not evident from product risk.

Findings, affected files, and solution hints are optional. Use them for a verified defect, refactoring requirement, or implemented constraint. Do not add them to a greenfield product requirement without code evidence.

## GitHub Projects traceability

Each requirement maps to one primary GitHub Projects item under the [development workflow](development/workflow.md). Use the requirement ID at the start of the item title, for example `[REQ-HOM-001] Present the product home page`, and link this document instead of copying its full text. If an item is too large for one reviewable change, use linked sub-issues without creating new product requirements.

Each item must identify its delivery wave, dependencies, acceptance evidence, and applicable cross-cutting requirements. Security, accessibility, performance, reliability, and maintainability requirements have their own verification items, but they also constrain every functional item that uses them.

| Wave | Outcome | Primary requirements |
|---:|---|---|
| 0 | Reproducible application and delivery baseline. | `REQ-TEC-001` to `REQ-TEC-005`, `REQ-TEC-007`, `REQ-MNT-001` to `REQ-MNT-004`, `REQ-SEC-005` |
| 1 | Early evidence for the highest-risk Google Wallet integration. | Technical spike linked to `REQ-PAR-003` to `REQ-PAR-005` and [ADR 002](architecture/decisions/002-google-wallet-delivery-adapter.md); it does not close those requirements. |
| 2 | Public product entry, discovery, and visual baseline. | `REQ-HOM-001`, `REQ-DIS-001` to `REQ-DIS-003`, `REQ-UX-001` to `REQ-UX-004`, `REQ-PER-001`, `REQ-TEC-006` |
| 3 | Organizer identity, workspace, and complete experience draft. | `REQ-ORG-001` to `REQ-ORG-003`, `REQ-ORG-007`, `REQ-EXP-001` to `REQ-EXP-004`, `REQ-SEC-001` to `REQ-SEC-004` |
| 4 | Publication, public QR, participant invitation, and business access. | `REQ-ORG-004` to `REQ-ORG-006`, `REQ-DIS-004`, `REQ-BUS-001` to `REQ-BUS-006` |
| 5 | Anonymous participation, private pass, and production Wallet card. | `REQ-PAR-001` to `REQ-PAR-006` |
| 6 | Authorized visit validation and unordered progress. | `REQ-VIS-001` to `REQ-VIS-006`, `REQ-PER-002` |
| 7 | Benefit allocation and authorized redemption. | `REQ-BEN-001` to `REQ-BEN-005`, `REQ-REL-001` |
| 8 | Reporting, audit, operational recovery, and release evidence. | `REQ-ANA-001`, `REQ-ANA-002`, `REQ-OPS-001`, `REQ-OPS-002`, `REQ-REL-002`, `REQ-OBS-001`, `REQ-TEC-008` |

The register sequence supports lookup and does not define implementation order. The wave number controls dependency order and does not replace MoSCoW priority. An item can move to `Ready` only when its required upstream behavior exists or the item includes that behavior.

Wave 0 installs Socialite and establishes the Google provider configuration contract for `REQ-TEC-003`. Wave 3 implements and verifies the organizer login callback and the safe identity-linking behavior required by `REQ-ORG-001` and `REQ-ORG-002`.

## Functional requirements

### Product home

#### REQ-HOM-001 — Present the product home page

**Priority:** Must | **Type:** Functional | **Module:** Product Home | **Source:** RD, MA, UX

**Description:** The public root page must explain the product for visitors, organizers, and participating businesses. It must show representative experience examples and provide primary actions to discover or create an experience.

**User Story**

> **As** a VISITOR OR ORGANIZER
>
> **I want** to understand the product from its home page
>
> **So that** I can decide whether to discover or create an experience without prior product knowledge.

**Acceptance criteria:**

> **Given** that a person opens the public root page without authentication
>
> **When** the page loads
>
> **Then** it explains the product, shows representative uses, and provides visible actions to discover and create experiences.

> **Given** that the person selects a primary action
>
> **When** navigation completes
>
> **Then** discovery opens publicly or experience creation starts through organizer authentication.

**Verification:** Responsive browser, content, navigation, and accessibility test.

### Discovery

#### REQ-DIS-001 — Discover experiences

**Priority:** Must | **Type:** Functional | **Module:** Discovery | **Source:** MA, UX

**Description:** The system must provide public discovery of active and upcoming experiences with basic location, date, category, audience, and state filters.

**User Story**

> **As** a VISITOR
>
> **I want** to filter available experiences
>
> **So that** I can find a relevant local plan without researching each business separately.

**Acceptance criteria:**

> **Given** that published experiences exist
>
> **When** the visitor applies supported discovery filters
>
> **Then** the system shows only matching active or upcoming experiences.

**Verification:** Browser test with representative filter combinations.

#### REQ-DIS-002 — View an experience

**Priority:** Must | **Type:** Functional | **Module:** Discovery | **Source:** CD, UX

**Description:** An experience page must show its identity, story, participants, unordered map, dates, `K/N` goal, benefit, conditions, organizer, and availability.

**User Story**

> **As** a VISITOR
>
> **I want** to understand an experience before I join
>
> **So that** I can decide whether its places, goal, and benefit suit me.

**Acceptance criteria:**

> **Given** that an experience is published
>
> **When** the visitor opens its public page
>
> **Then** all required public information is visible and the map does not imply a required order.

**Verification:** Browser and content test.

#### REQ-DIS-003 — Access and share public content

**Priority:** Must | **Type:** Functional | **Module:** Discovery | **Source:** CD

**Description:** Public content must be available without authentication and must provide shareable experience and participant acquisition links.

**User Story**

> **As** a VISITOR
>
> **I want** to open and share an experience without an account
>
> **So that** discovery does not add registration friction.

**Acceptance criteria:**

> **Given** that a user has no authenticated session
>
> **When** the user opens a public experience or participant link
>
> **Then** the content is available and can be shared without exposing a private credential.

**Verification:** Anonymous browser and link test.

#### REQ-DIS-004 — Open an experience from a public QR

**Priority:** Must | **Type:** Functional | **Module:** Discovery | **Source:** CD

**Description:** A public QR must open discovery, record only its acquisition source, and must not contain a private credential or create a visit.

**User Story**

> **As** a VISITOR
>
> **I want** to scan a public QR at a participating business
>
> **So that** I can discover the complete experience safely.

**Acceptance criteria:**

> **Given** that a participant displays its public QR
>
> **When** a visitor scans it
>
> **Then** the experience opens, the participant source is recorded, and no visit or private participation is created.

**Verification:** QR payload inspection and integration test.

### Organizer and experience

#### REQ-ORG-001 — Manage organizer identity

**Priority:** Must | **Type:** Functional | **Module:** Organizer Identity | **Source:** RD, CD

**Description:** An organizer must be able to register, authenticate, and recover access with Laravel authentication and must be able to use Google through Socialite.

**User Story**

> **As** an ORGANIZER
>
> **I want** to access the platform with Google or credentials
>
> **So that** I can create and manage my experiences securely.

**Acceptance criteria:**

> **Given** that the organizer selects a supported authentication method
>
> **When** valid identity evidence is provided
>
> **Then** the system creates or restores the correct organizer session.

> **Given** that a password organizer loses access
>
> **When** the recovery flow is completed
>
> **Then** the organizer can set new credentials without exposing the old password.

**Verification:** Authentication and recovery tests.

#### REQ-ORG-002 — Link organizer identities safely

**Priority:** Must | **Type:** Functional | **Module:** Organizer Identity | **Source:** CD

**Description:** Identity linking must prevent unsafe duplicate accounts and automatic account takeover when social and password identities overlap.

**User Story**

> **As** an ORGANIZER
>
> **I want** my Google and password identities linked only after safe verification
>
> **So that** another person cannot take over my account through a matching email.

**Acceptance criteria:**

> **Given** that a Google identity uses an email already associated with an organizer
>
> **When** the callback is processed without proof from the existing account
>
> **Then** the system does not link or replace the existing identity automatically.

**Verification:** Authentication security test.

#### REQ-ORG-003 — Manage an experience lifecycle

**Priority:** Must | **Type:** Functional | **Module:** Experience | **Source:** CD, UX

**Description:** An organizer must be able to create, save, resume, preview, publish, cancel, and close an owned experience.

**User Story**

> **As** an ORGANIZER
>
> **I want** to manage an experience from draft through closure
>
> **So that** I can prepare and operate it without platform intervention.

**Acceptance criteria:**

> **Given** that the organizer owns an experience
>
> **When** the organizer performs an allowed lifecycle action
>
> **Then** the experience enters the corresponding valid state and preserves its saved information.

**Verification:** Browser and domain state-transition tests.

#### REQ-ORG-004 — Enforce publication readiness

**Priority:** Must | **Type:** Functional | **Module:** Experience | **Source:** CD

**Description:** Publication must enforce the required configuration, at least two participants, and `2 ≤ K ≤ N`.

**User Story**

> **As** an ORGANIZER
>
> **I want** the platform to detect incomplete or inconsistent experience rules
>
> **So that** visitors never receive an unusable experience.

**Acceptance criteria:**

> **Given** that an experience is incomplete or has an invalid `K/N` rule
>
> **When** the organizer requests publication
>
> **Then** publication is rejected and the missing or invalid configuration is identified.

> **Given** that all publication rules pass
>
> **When** the organizer confirms publication
>
> **Then** the experience becomes publicly discoverable.

**Verification:** Domain and browser tests.

#### REQ-ORG-005 — Protect structural rules after activation

**Priority:** Must | **Type:** Functional | **Module:** Experience | **Source:** CD

**Description:** Structural rules must become immutable after the first participation. Safe public-content corrections can remain available.

**User Story**

> **As** a VISITOR
>
> **I want** the goal and benefit rules to remain stable after I join
>
> **So that** my participation cannot change unfairly.

**Acceptance criteria:**

> **Given** that an experience has at least one participation
>
> **When** the organizer attempts to change a structural rule
>
> **Then** the system rejects the change while allowing approved public-content corrections.

**Verification:** Domain authorization test.

#### REQ-ORG-006 — Deliver and manage participant invitations

**Priority:** Must | **Type:** Functional | **Module:** Experience | **Source:** CD, UX

**Description:** The organizer must be able to send, resend, copy, and revoke participant invitations and review readiness. Email is the primary delivery channel. A copyable private link is the fallback when email delivery is delayed or unavailable.

**User Story**

> **As** an ORGANIZER
>
> **I want** to manage participant invitations from one place
>
> **So that** each coordinated business can become operational before publication.

**Acceptance criteria:**

> **Given** that a participant belongs to an owned experience
>
> **When** the organizer sends, resends, copies, or revokes its invitation
>
> **Then** the delivery and activation states reflect the action without exposing the invitation token.

> **Given** that email delivery fails or the business needs another channel
>
> **When** the organizer requests the invitation link
>
> **Then** the system provides the current private link and does not create a second active invitation.

**Verification:** Browser, mail-adapter, token, and invitation-state tests.

#### REQ-ORG-007 — Use the organizer workspace

**Priority:** Must | **Type:** Functional | **Module:** Organizer Workspace | **Source:** CD, UX

**Description:** The organizer workspace must show owned experiences, lifecycle state, readiness, participant activation, and available key measures. It must provide direct actions to create an experience or continue an existing draft.

**User Story**

> **As** an ORGANIZER
>
> **I want** one workspace for my experiences
>
> **So that** I can identify the next required action without platform assistance.

**Acceptance criteria:**

> **Given** that an organizer owns experiences
>
> **When** the workspace opens
>
> **Then** it shows each experience state, readiness, participant activation, and the actions allowed in that state.

> **Given** that an organizer owns no experience
>
> **When** the workspace opens
>
> **Then** it explains the first step and provides one clear create action.

**Verification:** Authorization, browser, content, and empty-state tests.

### Experience builder

#### REQ-EXP-001 — Configure identity, schedule, place, and visual content

**Priority:** Must | **Type:** Functional | **Module:** Experience Builder | **Source:** CD, UX

**Description:** The organizer must be able to configure the public identity, description, category, audience, visual content, visit and redemption periods, locality, and venue or distributed mode of an owned draft.

**User Story**

> **As** an ORGANIZER
>
> **I want** to describe when, where, and why an experience exists
>
> **So that** visitors can understand it and decide whether to participate.

**Acceptance criteria:**

> **Given** that an organizer edits an owned draft
>
> **When** valid identity, schedule, place, and visual content are saved
>
> **Then** the configuration persists and appears in the experience preview.

> **Given** that visual content has a disallowed type, size, dimensions, or decoded content
>
> **When** the organizer uploads it
>
> **Then** the system rejects it safely and explains the supported limits.

**Verification:** Browser, validation, media-security, and persistence tests.

#### REQ-EXP-002 — Manage participating businesses

**Priority:** Must | **Type:** Functional | **Module:** Experience Builder | **Source:** MA, CD, UX

**Description:** The organizer must be able to add, edit, and remove participating businesses before structural lock. The builder must support individual entry and a line-separated name list, then allow optional details and invitation contacts to be completed later.

**User Story**

> **As** an ORGANIZER
>
> **I want** to load businesses with minimal initial data
>
> **So that** setup remains practical for an experience with many participants.

**Acceptance criteria:**

> **Given** that an organizer has a list of coordinated businesses
>
> **When** the organizer pastes one valid name per line
>
> **Then** the builder creates editable participant drafts without requiring every optional detail.

> **Given** that a participant draft exists before structural lock
>
> **When** the organizer adds its public location and invitation contact
>
> **Then** the business becomes eligible for publication-readiness checks and invitation.

**Verification:** Browser, parsing, validation, authorization, and persistence tests.

#### REQ-EXP-003 — Configure the goal and benefit

**Priority:** Must | **Type:** Functional | **Module:** Experience Builder | **Source:** CD, UX

**Description:** The organizer must be able to configure the `K/N` goal and one benefit with clear conditions, capacity, validity, responsible owner, and authorized redemption points.

**User Story**

> **As** an ORGANIZER
>
> **I want** to define one clear completion goal and benefit
>
> **So that** visitors and businesses understand the shared commitment before publication.

**Acceptance criteria:**

> **Given** that an organizer configures an owned draft
>
> **When** a valid `K/N` goal and benefit responsibility are saved
>
> **Then** the preview shows the goal, conditions, capacity state, validity, and authorized redemption points consistently.

> **Given** that the goal, capacity, dates, responsible owner, or redemption points are inconsistent
>
> **When** the organizer saves or requests publication
>
> **Then** the system identifies the invalid rule and does not publish the experience.

**Verification:** Domain, validation, and browser tests.

#### REQ-EXP-004 — Preview the experience before publication

**Priority:** Must | **Type:** Functional | **Module:** Experience Builder | **Source:** CD, UX

**Description:** The organizer must be able to preview the public landing page, Google Wallet information hierarchy, and printable public QR material before publication.

**User Story**

> **As** an ORGANIZER
>
> **I want** to review every visitor-facing surface before publication
>
> **So that** I can correct unclear information without creating test participations or public material.

**Acceptance criteria:**

> **Given** that an owned experience remains a draft
>
> **When** the organizer opens its preview
>
> **Then** the landing, Wallet card, and QR material reflect the current configuration and are clearly marked as previews.

> **Given** that a preview is open
>
> **When** a preview action or QR is exercised
>
> **Then** it does not publish the experience, issue a production Wallet card, or create a participation.

**Verification:** Browser, QR payload, Wallet-adapter boundary, and domain-state tests.

### Participating business

#### REQ-BUS-001 — Explain an invitation before activation

**Priority:** Must | **Type:** Functional | **Module:** Business Access | **Source:** CD, UX

**Description:** An invitation must identify the organizer, experience, participant, expiry, and allowed operations before activation.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** to understand who invited me and what access I will receive
>
> **So that** I can activate the correct experience confidently.

**Acceptance criteria:**

> **Given** that an invitation token is valid
>
> **When** the business opens it
>
> **Then** the system shows its identity, scope, expiry, and permissions before any device is registered.

**Verification:** Browser content test.

#### REQ-BUS-002 — Activate limited business access

**Priority:** Must | **Type:** Functional | **Module:** Business Access | **Source:** CD

**Description:** First activation must use an expiring one-time token, register one device, and define a PIN. Later access must require that device context and PIN.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** to activate a limited validator without creating a full account
>
> **So that** I can operate the experience with minimal setup.

**Acceptance criteria:**

> **Given** that a valid unused invitation exists
>
> **When** the business registers its device and defines a valid PIN
>
> **Then** the invitation is consumed and limited access becomes active on that device.

> **Given** that activation is complete
>
> **When** a later session starts
>
> **Then** the system requires the registered device context and correct PIN.

**Verification:** Security, browser, and one-time-token tests.

#### REQ-BUS-003 — Scope and revoke business access

**Priority:** Must | **Type:** Functional | **Module:** Business Access | **Source:** CD

**Description:** Business access must be revocable and limited to one participant in one experience.

**User Story**

> **As** an ORGANIZER
>
> **I want** to revoke and constrain each business access
>
> **So that** a participant cannot operate outside the experience context assigned to it.

**Acceptance criteria:**

> **Given** that business access is active
>
> **When** it requests another participant or experience
>
> **Then** access is denied.

> **Given** that the organizer revokes the access
>
> **When** the business attempts another protected operation
>
> **Then** the system requires a new valid activation.

**Verification:** Authorization and revocation tests.

#### REQ-BUS-004 — Operate the participant workspace

**Priority:** Must | **Type:** Functional | **Module:** Business Access | **Source:** CD, UX

**Description:** An active operator must be able to download printable public QR material, copy its shareable participant link, validate by camera or manual code, perform authorized redemption, and view its own activity.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** one focused workspace for my allowed operations
>
> **So that** I can serve visitors without navigating a complex administration product.

**Acceptance criteria:**

> **Given** that business access is active
>
> **When** the operator opens the workspace
>
> **Then** printable public QR material, the shareable participant link, camera and manual validation, authorized redemption, and own activity are available according to scope.

> **Given** that the operator downloads or shares public acquisition material
>
> **When** a visitor uses its QR or link
>
> **Then** the experience opens with participant-source attribution and no visit is created.

**Verification:** Mobile browser end-to-end test.

#### REQ-BUS-005 — Protect experience rules and peer data

**Priority:** Must | **Type:** Functional | **Module:** Business Access | **Source:** CD

**Description:** A business must not edit experience rules or view another participant's operational data.

**User Story**

> **As** an ORGANIZER
>
> **I want** each business restricted to its operational responsibility
>
> **So that** experience governance and participant confidentiality remain under control.

**Acceptance criteria:**

> **Given** that a business operator is active
>
> **When** it requests rule changes or another participant's data
>
> **Then** the server denies the operation and returns no protected data.

**Verification:** Authorization and data-scope tests.

#### REQ-BUS-006 — Recover a business device

**Priority:** Should | **Type:** Functional | **Module:** Business Access | **Source:** CD

**Description:** A lost or revoked device should recover through a new organizer-issued activation.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** a controlled recovery path after device loss
>
> **So that** I can resume validation without a permanent account.

**Acceptance criteria:**

> **Given** that the prior device is lost or revoked
>
> **When** the organizer issues and the business completes a new activation
>
> **Then** the new device becomes active and the prior access remains invalid.

**MoSCoW rationale:** Should — Recovery reduces business abandonment, but the organizer can issue a replacement invitation manually if this flow is delayed.

**Verification:** Recovery and revocation test.

### Visitor participation and Wallet

#### REQ-PAR-001 — Activate an anonymous participation

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD

**Description:** A visitor must be able to activate one anonymous participation without name, email, phone, password, or product account.

**User Story**

> **As** a VISITOR
>
> **I want** to activate my participation without registration
>
> **So that** I can start the experience with minimal friction.

**Acceptance criteria:**

> **Given** that an experience is published and available
>
> **When** the visitor activates participation
>
> **Then** the system creates one anonymous participation without requesting identity or contact data.

**Verification:** Browser and persisted-data inspection.

#### REQ-PAR-002 — Reopen and use a private web pass

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD, UX

**Description:** The system must provide one private web pass with progress, visits, benefit state, validation credential, and essential experience information. The visitor must be able to reopen it through its opaque private URL or the link stored in Google Wallet without a conventional login.

**User Story**

> **As** a VISITOR
>
> **I want** to access my participation from a private web pass
>
> **So that** I can review and use it when Wallet is unavailable.

**Acceptance criteria:**

> **Given** that a participation exists
>
> **When** the visitor opens it with a valid private-view credential
>
> **Then** the pass shows current participation information and the validation action.

> **Given** that the visitor returns after closing the original activation page
>
> **When** the visitor uses the saved private URL or the Wallet link
>
> **Then** the same participation opens without requesting an account or personal data.

> **Given** that the private-view credential is missing or invalid
>
> **When** the page is requested
>
> **Then** the system reveals no participation information.

**Verification:** Browser, authorization, and content tests.

#### REQ-PAR-003 — Add the card to Google Wallet

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD, UX

**Description:** The system must issue a Google Wallet representation with identity, progress, benefit state, validity, private QR, and a private-pass link.

**User Story**

> **As** a VISITOR
>
> **I want** to add my experience card to Google Wallet
>
> **So that** I can present it quickly without installing another application.

**Acceptance criteria:**

> **Given** that participation activation succeeds
>
> **When** the visitor selects the Google Wallet action
>
> **Then** a valid card for that participation is available with the required information and links.

**Verification:** Provider contract, sandbox, and Android device test.

#### REQ-PAR-004 — Synchronize Wallet state

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD, RD

**Description:** Accepted visits, benefit availability, redemption, and relevant experience state changes must request a Wallet update.

**User Story**

> **As** a VISITOR
>
> **I want** my Wallet card to reflect accepted changes
>
> **So that** its progress and benefit state remain useful during the experience.

**Acceptance criteria:**

> **Given** that a Wallet-backed participation changes in a relevant way
>
> **When** the domain transaction commits
>
> **Then** the system requests the corresponding Wallet update after the commit.

**Verification:** Provider adapter and queue integration test.

#### REQ-PAR-005 — Continue when Wallet fails

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD

**Description:** Wallet failure must not invalidate the participation or its private web pass.

**User Story**

> **As** a VISITOR
>
> **I want** my participation to remain usable during a Wallet failure
>
> **So that** an external provider does not block the experience.

**Acceptance criteria:**

> **Given** that participation exists
>
> **When** Wallet issuance or update fails
>
> **Then** the private web pass remains valid and the Wallet operation can be retried safely.

**Verification:** Provider failure-path test.

#### REQ-PAR-006 — Separate participation credentials

**Priority:** Must | **Type:** Functional | **Module:** Participation | **Source:** CD

**Description:** Private-view and validation access must use separate opaque credentials.

**User Story**

> **As** a VISITOR
>
> **I want** viewing and validation access to be separated
>
> **So that** presenting my card does not expose unnecessary control over my private pass.

**Acceptance criteria:**

> **Given** that a participation is created
>
> **When** its credentials are inspected or exercised
>
> **Then** the private-view credential and validation credential are distinct, opaque, and limited to their intended operations.

**Verification:** Security and credential-scope test.

### Visits and progress

#### REQ-VIS-001 — Authorize validation scope and period

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD

**Description:** Only active business access for a participant in the same experience can request validation during the published visit period. Cancelled, closed, upcoming, and expired experiences must reject new visits.

**User Story**

> **As** a VISITOR
>
> **I want** only the business I am visiting to validate its visit
>
> **So that** another participant cannot alter my progress.

**Acceptance criteria:**

> **Given** that a validation credential belongs to one experience
>
> **When** a business outside the matching active scope requests validation
>
> **Then** the system denies the request.

> **Given** that business scope and participation match
>
> **When** validation is requested outside the published visit period or for a cancelled or closed experience
>
> **Then** the system rejects the visit and returns the applicable state without changing progress.

**Verification:** Authorization test across businesses and experiences.

#### REQ-VIS-002 — Review before confirming a visit

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD, UX

**Description:** Lookup must show validation context and expected effect and must not create a visit before explicit confirmation.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** to review the requested validation before confirming it
>
> **So that** scanning mistakes do not create visits automatically.

**Acceptance criteria:**

> **Given** that the operator scans or enters a valid code
>
> **When** lookup completes
>
> **Then** the system shows the experience, business, current progress, and expected effect without creating a visit.

**Verification:** Browser and domain-state test.

#### REQ-VIS-003 — Record an immutable visit

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD

**Description:** Accepted confirmation must create one immutable visit with the validating participant and time.

**User Story**

> **As** an ORGANIZER
>
> **I want** every accepted confirmation recorded as an immutable visit
>
> **So that** progress and reporting have reliable evidence.

**Acceptance criteria:**

> **Given** that an authorized validation is ready
>
> **When** the business explicitly confirms it
>
> **Then** the system stores one visit with its participation, validating participant, result, and time.

**Verification:** Domain and database integration test.

#### REQ-VIS-004 — Count distinct and repeated visits

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD

**Description:** The first accepted visit to a participant must increase distinct progress. Repeated visits must increase total visits only.

**User Story**

> **As** a VISITOR
>
> **I want** progress based on different businesses
>
> **So that** repeated visits do not incorrectly complete the experience.

**Acceptance criteria:**

> **Given** that a participation has not visited a business
>
> **When** its first visit is accepted
>
> **Then** total visits and distinct progress each increase once.

> **Given** that the same business was visited before
>
> **When** another visit is accepted
>
> **Then** total visits increase and distinct progress does not change.

**Verification:** Domain test for first and repeated visits.

#### REQ-VIS-005 — Keep progress unordered

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD

**Description:** Visit order must not affect progress or eligibility. Goal completion must not block later eligible visits or create a second entitlement.

**User Story**

> **As** a VISITOR
>
> **I want** to choose businesses in any order
>
> **So that** the experience adapts to my interests, location, and available time.

**Acceptance criteria:**

> **Given** that a visitor selects any eligible participant
>
> **When** an authorized visit is accepted
>
> **Then** progress follows the same rules regardless of prior visit order.

> **Given** that the goal is already complete
>
> **When** another eligible visit is accepted
>
> **Then** the visit is recorded without creating another entitlement.

**Verification:** Domain permutation and post-completion tests.

#### REQ-VIS-006 — Process validation idempotently

**Priority:** Must | **Type:** Functional | **Module:** Visit and Progress | **Source:** CD, UX

**Description:** Validation must be idempotent and must return a clear accepted, repeated, rejected, or already-processed result.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** retries to produce one clear result
>
> **So that** connectivity or repeated taps do not duplicate visits.

**Acceptance criteria:**

> **Given** that the same validation request is submitted more than once
>
> **When** the requests are processed
>
> **Then** one domain result is created and every response identifies the final state clearly.

**Verification:** Concurrency, idempotency, and browser-result tests.

### Benefit and redemption

#### REQ-BEN-001 — Create one benefit entitlement

**Priority:** Must | **Type:** Functional | **Module:** Benefit | **Source:** CD

**Description:** Reaching `K` distinct participants must create at most one benefit entitlement.

**User Story**

> **As** a VISITOR
>
> **I want** the promised benefit enabled when I complete the goal
>
> **So that** the experience recognizes my progress exactly once.

**Acceptance criteria:**

> **Given** that a participation reaches `K` distinct participants
>
> **When** the completing visit commits
>
> **Then** one entitlement is created or the existing entitlement is returned.

**Verification:** Domain and concurrency test.

#### REQ-BEN-002 — Reserve limited capacity atomically

**Priority:** Must | **Type:** Functional | **Module:** Benefit | **Source:** CD

**Description:** Limited benefit capacity must be reserved atomically under the published rule.

**User Story**

> **As** an ORGANIZER
>
> **I want** limited benefits allocated without exceeding capacity
>
> **So that** the published commitment remains operationally viable.

**Acceptance criteria:**

> **Given** that one benefit unit remains
>
> **When** concurrent participations complete the goal
>
> **Then** at most one receives the unit and stored capacity never becomes invalid.

**Verification:** PostgreSQL concurrency test.

#### REQ-BEN-003 — Show the benefit lifecycle

**Priority:** Must | **Type:** Functional | **Module:** Benefit | **Source:** CD, UX

**Description:** The private web pass and Wallet card must distinguish available, redeemed, and expired benefit states.

**User Story**

> **As** a VISITOR
>
> **I want** to understand the current state of my benefit
>
> **So that** I know whether, where, and when I can use it.

**Acceptance criteria:**

> **Given** that an entitlement changes state
>
> **When** the visitor opens the private pass or refreshed Wallet card
>
> **Then** the correct state and applicable conditions are shown with text and not color alone.

**Verification:** Browser, accessibility, and provider tests.

#### REQ-BEN-004 — Authorize benefit redemption

**Priority:** Must | **Type:** Functional | **Module:** Benefit | **Source:** CD

**Description:** Only an authorized redemption point can redeem an available entitlement during its period and after reviewing its conditions.

**User Story**

> **As** a REDEMPTION OPERATOR
>
> **I want** to review and redeem only eligible benefits
>
> **So that** the published conditions are enforced consistently.

**Acceptance criteria:**

> **Given** that an available entitlement and authorized redemption access exist
>
> **When** the operator reviews and confirms redemption during the valid period
>
> **Then** the system accepts the redemption.

> **Given** that access, state, or time is invalid
>
> **When** redemption is requested
>
> **Then** the system rejects it without changing the entitlement.

**Verification:** Authorization, time-rule, and browser tests.

#### REQ-BEN-005 — Finalize redemption safely

**Priority:** Must | **Type:** Functional | **Module:** Benefit | **Source:** CD

**Description:** Redemption must be final and idempotent and must not reset participation or visits.

**User Story**

> **As** an ORGANIZER
>
> **I want** each benefit redeemed once without erasing history
>
> **So that** disputes and repeated claims can be resolved from reliable records.

**Acceptance criteria:**

> **Given** that the same redemption is confirmed or retried more than once
>
> **When** the requests are processed
>
> **Then** one final redemption exists and participation history remains unchanged.

**Verification:** Domain, concurrency, and audit test.

### Analytics and operation

#### REQ-ANA-001 — Report experience measures

**Priority:** Must | **Type:** Functional | **Module:** Reporting and Audit | **Source:** MA, CD

**Description:** The organizer must see discovery, activation, business readiness, visit distribution, completion, benefit, redemption, and failure measures for owned experiences.

**User Story**

> **As** an ORGANIZER
>
> **I want** essential measures for my experiences
>
> **So that** I can understand participation and operational friction.

**Acceptance criteria:**

> **Given** that an organizer owns an experience with recorded activity
>
> **When** the organizer opens its report
>
> **Then** the approved measures use only that experience's data and consistent definitions.

**Verification:** Reporting calculation and authorization test.

#### REQ-ANA-002 — Limit business reporting

**Priority:** Must | **Type:** Functional | **Module:** Reporting and Audit | **Source:** MA, CD

**Description:** A business must see only its own activity, and reports must not label visits as sales or economic impact.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** a truthful summary of my own activity
>
> **So that** I receive useful information without exposure of peer data or unsupported claims.

**Acceptance criteria:**

> **Given** that business access is active
>
> **When** the operator opens reporting
>
> **Then** only its participant activity appears and all labels describe recorded interactions rather than sales or economic impact.

**Verification:** Authorization and content test.

#### REQ-OPS-001 — Audit sensitive operations

**Priority:** Must | **Type:** Functional | **Module:** Reporting and Audit | **Source:** CD

**Description:** Sensitive activation, validation, redemption, revocation, and platform suspension actions must create audit evidence.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** sensitive actions recorded with useful context
>
> **So that** I can investigate fraud, disputes, and operational failures.

**Acceptance criteria:**

> **Given** that a sensitive operation succeeds or fails
>
> **When** its final result is known
>
> **Then** the system records actor scope, action, target, result, time, and correlation identifier without raw credentials.

**Verification:** Audit integration and sensitive-data inspection.

#### REQ-OPS-002 — Suspend abuse or compromised access

**Priority:** Should | **Type:** Functional | **Module:** Platform Operations | **Source:** CD

**Description:** Platform operation should be able to suspend abusive content or compromised access without taking organizer ownership.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** a limited suspension capability
>
> **So that** I can contain verified abuse or compromise without administering the organizer's experience.

**Acceptance criteria:**

> **Given** that abuse or compromise is confirmed
>
> **When** the operator applies an authorized suspension
>
> **Then** the affected content or access becomes unavailable and the action is audited without changing organizer ownership.

**MoSCoW rationale:** Should — Basic revocation covers immediate MVP01 risk. A dedicated platform suspension workflow becomes necessary when public use produces verified abuse.

**Verification:** Operations authorization and audit test.

## Quality requirements

### Security and privacy

#### REQ-SEC-001 — Deny protected access by default

**Priority:** Must | **Type:** Security | **Module:** Security | **Source:** CD

**Description:** Protected resources and actions must use deny-by-default server authorization.

**User Story**

> **As** a PRODUCT OWNER
>
> **I want** every protected operation authorized on the server
>
> **So that** missing client controls cannot expose or modify restricted information.

**Acceptance criteria:**

> **Given** that a request has no explicit permission for the target resource and action
>
> **When** the server processes it
>
> **Then** access is denied without returning protected data.

**Verification:** Policy and abuse-case tests.

#### REQ-SEC-002 — Apply framework and domain protections

**Priority:** Must | **Type:** Security | **Module:** Security | **Source:** RD, CD

**Description:** The product must use Laravel web protections and must add rate limits, confirmation, and idempotency to sensitive domain actions.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** framework protections extended at product-specific boundaries
>
> **So that** common web attacks and repeated sensitive operations are controlled without duplicate security code.

**Acceptance criteria:**

> **Given** that a protected mutation or sensitive action is requested
>
> **When** the server processes it
>
> **Then** applicable CSRF, validation, authorization, rate-limit, confirmation, and idempotency controls are enforced.

**Verification:** Configuration, integration, and abuse-case tests.

#### REQ-SEC-003 — Protect secrets and private credentials

**Priority:** Must | **Type:** Security | **Module:** Security | **Source:** WS

**Description:** Secrets, tokens, private codes, and PINs must not appear in source control, avoidable URLs, logs, or recoverable storage when hashing applies.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** sensitive values stored and transmitted according to their purpose
>
> **So that** a source, log, or database disclosure has limited impact.

**Acceptance criteria:**

> **Given** that a secret or private credential is created or used
>
> **When** source, logs, URLs, persistence, and error output are inspected
>
> **Then** the value is absent, redacted, or irreversibly hashed wherever recovery is not required.

**Verification:** Secret scan, log inspection, and storage review.

#### REQ-SEC-004 — Minimize and retain visitor data

**Priority:** Must | **Type:** Security | **Module:** Security | **Source:** CD

**Description:** The product must collect only operationally required visitor data, expose aggregate organizer reporting, and apply a documented retention rule to expired participation and technical data before production deployment.

**User Story**

> **As** a VISITOR
>
> **I want** to participate without surrendering identity or contact data
>
> **So that** local discovery does not create unnecessary privacy risk.

**Acceptance criteria:**

> **Given** that a visitor activates and uses a participation
>
> **When** stored data and organizer reports are inspected
>
> **Then** no name, email, phone, or demographic data is required and reporting remains aggregated.

> **Given** that participation or technical data exceeds its approved retention period
>
> **When** the retention process runs
>
> **Then** the data is deleted or irreversibly aggregated unless a documented operational, security, or legal reason requires it.

**Verification:** Data model, form, reporting, retention-policy, and cleanup-process inspection.

#### REQ-SEC-005 — Detect delivery security failures

**Priority:** Must | **Type:** Security | **Module:** Security | **Source:** WS

**Description:** Delivery must detect leaked secrets, vulnerable dependencies, unsafe production configuration, and access-control regressions.

**User Story**

> **As** a MAINTAINER
>
> **I want** lightweight automated security gates
>
> **So that** common preventable weaknesses do not reach production.

**Acceptance criteria:**

> **Given** that a change contains a detectable secret, vulnerable dependency, unsafe production setting, or authorization regression
>
> **When** required checks run
>
> **Then** delivery fails with actionable evidence.

**Verification:** Controlled CI failure tests and release checklist.

### Usability and accessibility

#### REQ-UX-001 — Meet the accessibility baseline

**Priority:** Must | **Type:** Usability | **Module:** UI/UX | **Source:** UX

**Description:** Critical flows must target WCAG 2.2 AA and remain usable with keyboard, touch, zoom, and reduced motion.

**User Story**

> **As** a VISITOR OR OPERATOR
>
> **I want** critical tasks to work with my access method
>
> **So that** disability or device interaction does not block participation.

**Acceptance criteria:**

> **Given** that a user operates a critical flow with keyboard, touch, `200%` zoom, or reduced motion
>
> **When** the task is completed
>
> **Then** content, focus, labels, controls, and feedback remain perceivable and operable at the WCAG 2.2 AA target.

**Verification:** Automated accessibility scan and manual assistive-interaction review.

#### REQ-UX-002 — Keep the neon identity accessible

**Priority:** Must | **Type:** Usability | **Module:** UI/UX | **Source:** UX

**Description:** The interface must use the dark neon identity without using color, glow, or motion as the only state indicator.

**User Story**

> **As** a USER
>
> **I want** the visual identity to remain clear and readable
>
> **So that** the expressive design does not hide status or actions.

**Acceptance criteria:**

> **Given** that the interface shows an action, status, warning, or error
>
> **When** color, glow, or motion is unavailable or not perceived
>
> **Then** text, iconography, structure, or focus still communicates its meaning.

**Verification:** Visual, contrast, and accessibility review.

#### REQ-UX-003 — Support mobile operation

**Priority:** Must | **Type:** Usability | **Module:** UI/UX | **Source:** UX

**Description:** Public and business flows must be mobile first and must preserve camera, manual-code, and recovery actions on small screens.

**User Story**

> **As** a VISITOR OR BUSINESS OPERATOR
>
> **I want** the complete critical flow on a mobile device
>
> **So that** I can use the product where discovery and validation occur.

**Acceptance criteria:**

> **Given** that a supported small-screen device is used
>
> **When** the visitor or operator completes a critical task
>
> **Then** primary actions remain visible and camera failure has a manual-code recovery path.

**Verification:** Responsive Android-profile browser test.

#### REQ-UX-004 — Explain the unordered experience

**Priority:** Must | **Type:** Usability | **Module:** UI/UX | **Source:** CD, UX

**Description:** User-facing text must use domain terminology and must state that participants can be visited in any order.

**User Story**

> **As** a VISITOR
>
> **I want** the experience rules explained in familiar language
>
> **So that** I do not mistake it for a mandatory route or loyalty-points program.

**Acceptance criteria:**

> **Given** that an experience or participation page explains the model
>
> **When** the content is reviewed
>
> **Then** it uses approved domain terms and states that participant order is unrestricted.

**Verification:** Content and terminology review.

### Performance and reliability

#### REQ-PER-001 — Meet public-page performance targets

**Priority:** Should | **Type:** Performance | **Module:** Performance | **Source:** UX

**Description:** Public pages should meet LCP `≤ 2.5 s`, INP `≤ 200 ms`, and CLS `≤ 0.1` at the 75th percentile under documented conditions.

**User Story**

> **As** a VISITOR
>
> **I want** discovery pages to load and respond quickly
>
> **So that** poor connectivity or a modest device does not cause abandonment.

**Acceptance criteria:**

> **Given** that the documented device, network, dataset, and measurement method are active
>
> **When** representative public pages are measured
>
> **Then** their 75th-percentile LCP, INP, and CLS meet the target or an exception is documented.

**MoSCoW rationale:** Should — These targets reduce discovery abandonment, but external hosting and early sample size can prevent reliable percentile evidence before release.

**Verification:** Lighthouse before traffic and field measurement after a meaningful sample exists.

#### REQ-PER-002 — Meet validation latency target

**Priority:** Should | **Type:** Performance | **Module:** Performance | **Source:** CD, UX

**Description:** Validation should complete within `2 s` at p95 under the documented baseline load, excluding asynchronous provider updates.

**User Story**

> **As** a BUSINESS OPERATOR
>
> **I want** validation to complete while the visitor is present
>
> **So that** counter operation remains practical.

**Acceptance criteria:**

> **Given** that the documented baseline load and dataset are active
>
> **When** validation requests are measured
>
> **Then** at least 95% complete within `2 s` without waiting for Wallet synchronization.

**MoSCoW rationale:** Should — Fast validation is operationally important, but the target needs a measured baseline on the selected free-tier deployment.

**Verification:** Load and end-to-end latency test.

#### REQ-REL-001 — Preserve transactions during provider failure

**Priority:** Must | **Type:** Reliability | **Module:** Reliability | **Source:** CD

**Description:** Provider failure must not reverse an accepted domain transaction. Failed Wallet updates must support safe retry.

**User Story**

> **As** a VISITOR
>
> **I want** an accepted visit or redemption preserved when an external provider fails
>
> **So that** provider availability does not corrupt my participation.

**Acceptance criteria:**

> **Given** that a domain transaction commits and a subsequent provider call fails
>
> **When** the operation completes
>
> **Then** the accepted domain state remains authoritative and the external update is marked for safe retry.

**Verification:** Provider failure and retry test.

#### REQ-REL-002 — Back up and restore production data

**Priority:** Must | **Type:** Reliability | **Module:** Reliability | **Source:** WS

**Description:** Production must have automated daily database backups and a verified restore procedure with initial RPO `24 h` and RTO `8 h`.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** recoverable production data
>
> **So that** a database incident does not permanently destroy experience activity.

**Acceptance criteria:**

> **Given** that production data exists
>
> **When** the scheduled backup and restore procedure are exercised
>
> **Then** restored data meets the documented `24 h` RPO and `8 h` RTO objectives.

**Verification:** Backup schedule inspection and restore exercise.

### Maintainability and observability

#### REQ-MNT-001 — Use the approved architecture

**Priority:** Must | **Type:** Maintainability | **Module:** Architecture | **Source:** RD

**Description:** The application must use a modular monolith with hexagonal architecture and explicit business-module ownership.

**User Story**

> **As** a MAINTAINER
>
> **I want** business behavior organized by module and inward dependencies
>
> **So that** the product can evolve without unnecessary distributed-system cost.

**Acceptance criteria:**

> **Given** that application code is added or changed
>
> **When** module ownership and dependencies are inspected
>
> **Then** the change belongs to one defined module and adapters depend inward without cyclic module dependencies.

**Verification:** Architecture review and dependency test.

#### REQ-MNT-002 — Keep domain rules independent

**Priority:** Must | **Type:** Maintainability | **Module:** Architecture | **Source:** RD, WS

**Description:** Domain rules must remain independent from Livewire, Eloquent, and provider SDK objects.

**User Story**

> **As** a MAINTAINER
>
> **I want** critical domain rules independent from delivery and persistence frameworks
>
> **So that** I can test and change adapters without rewriting business behavior.

**Acceptance criteria:**

> **Given** that domain code implements experience, visit, progress, entitlement, or redemption rules
>
> **When** its dependencies are inspected
>
> **Then** no Livewire, Eloquent, or provider SDK type enters the domain boundary.

**Verification:** Automated architecture test.

#### REQ-MNT-003 — Isolate provider coupling

**Priority:** Must | **Type:** Maintainability | **Module:** Architecture | **Source:** RD, WS

**Description:** Wallet, mail, mapping, storage, OAuth, and monitoring must use narrow project-owned ports when provider coupling crosses into the application.

**User Story**

> **As** a MAINTAINER
>
> **I want** volatile provider behavior isolated behind narrow ports
>
> **So that** external changes do not spread through product use cases.

**Acceptance criteria:**

> **Given** that application behavior needs an external provider
>
> **When** the dependency crosses the application boundary
>
> **Then** the application uses a narrow project-owned contract and the concrete SDK remains in an adapter.

**Verification:** Architecture and provider contract tests.

#### REQ-MNT-004 — Justify abstractions and patterns

**Priority:** Must | **Type:** Maintainability | **Module:** Architecture | **Source:** RD

**Description:** Services, repositories, and interfaces must have a current use-case, domain, persistence, provider, or test justification. Generic repositories and one service per model are not required.

**User Story**

> **As** a MAINTAINER
>
> **I want** abstractions introduced only for current boundaries
>
> **So that** SOLID, DRY, KISS, and YAGNI improve the code instead of creating ceremony.

**Acceptance criteria:**

> **Given** that a change introduces a service, repository, or interface
>
> **When** the change is reviewed
>
> **Then** it identifies the current responsibility or dependency boundary and does not add a generic repository or speculative layer.

**Verification:** Architecture review against the active use case.

#### REQ-OBS-001 — Emit actionable operational evidence

**Priority:** Must | **Type:** Observability | **Module:** Observability | **Source:** WS

**Description:** Production must emit health state, structured errors, correlation context, and audit events without credentials or unnecessary visitor data.

**User Story**

> **As** a PLATFORM OPERATOR
>
> **I want** actionable production evidence
>
> **So that** I can determine what failed, where it failed, how often it occurs, and which flow is affected.

**Acceptance criteria:**

> **Given** that a monitored request, job, or sensitive operation completes
>
> **When** operational evidence is emitted
>
> **Then** it contains useful release and correlation context without raw credentials or unnecessary visitor information.

**Verification:** Observability integration and sensitive-data inspection.

## Technology constraints

#### REQ-TEC-001 — Use PHP and Laravel

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** WS

**Description:** The application must use PHP 8.4 and Laravel 13.

**User Story**

> **As** a MAINTAINER
>
> **I want** the approved PHP and Laravel versions
>
> **So that** the project starts on one supported and consistent backend baseline.

**Acceptance criteria:**

> **Given** that the application is initialized or built
>
> **When** runtime and locked dependencies are inspected
>
> **Then** they require PHP 8.4 and Laravel 13.

**Verification:** Dependency manifest and CI runtime inspection.

#### REQ-TEC-002 — Use the Livewire starter kit

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** WS

**Description:** The project must start from the official Laravel Livewire starter kit with Fortify.

**User Story**

> **As** a MAINTAINER
>
> **I want** the official starter kit baseline
>
> **So that** authentication, layouts, and supported conventions do not require unnecessary custom scaffolding.

**Acceptance criteria:**

> **Given** that the project bootstrap is complete
>
> **When** authentication and frontend dependencies are inspected
>
> **Then** the official Livewire starter-kit structure and Fortify capabilities are present.

**Verification:** Project structure and authentication smoke test.

#### REQ-TEC-003 — Use Google social authentication

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** RD

**Description:** The application baseline must install Laravel Socialite and define Google as the organizer social-authentication provider. The functional login and safe account-linking flow is delivered with organizer identity in Wave 3.

**User Story**

> **As** an ORGANIZER
>
> **I want** to access the platform with Google
>
> **So that** I can reduce credential setup while keeping product authorization under **DeTuristaAndo** control.

**Acceptance criteria — Wave 0 baseline:**

> **Given** that the application bootstrap is complete
>
> **When** dependencies and environment configuration are inspected
>
> **Then** Socialite is installed and the Google client, secret, and redirect configuration contract is present without committed credentials.

**Acceptance criteria — Wave 3 integration:**

> **Given** that Google OAuth is configured
>
> **When** an organizer completes a valid callback
>
> **Then** Socialite supplies the verified external identity to the safe organizer-linking flow.

**Verification:** Wave 0 dependency and configuration inspection; Wave 3 Socialite integration and sandbox authentication test.

#### REQ-TEC-004 — Use the presentation stack

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** RD

**Description:** The presentation layer must use Blade, Livewire 4, Alpine.js, Tailwind CSS 4, and Flux UI Free.

**User Story**

> **As** a MAINTAINER
>
> **I want** one Laravel-centered presentation stack
>
> **So that** the product remains cohesive without a separate SPA architecture.

**Acceptance criteria:**

> **Given** that a product screen is implemented
>
> **When** its presentation dependencies are reviewed
>
> **Then** it uses the approved stack and introduces no alternative frontend framework without a superseding decision.

**Verification:** Dependency and architecture review.

#### REQ-TEC-005 — Use PostgreSQL

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** WS

**Description:** PostgreSQL 16 must be the authoritative data store.

**User Story**

> **As** a MAINTAINER
>
> **I want** one transactional relational store
>
> **So that** concurrency-sensitive visits, capacity, and redemption rules have reliable database enforcement.

**Acceptance criteria:**

> **Given** that persistence and CI environments are configured
>
> **When** migrations and integration tests run
>
> **Then** PostgreSQL 16 stores authoritative product state and enforces required constraints.

**Verification:** Environment inspection and PostgreSQL integration test.

#### REQ-TEC-006 — Use the approved map stack

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** WS

**Description:** Maps must use Leaflet with OpenStreetMap-compatible data and an approved tile service.

**User Story**

> **As** a VISITOR
>
> **I want** participant locations shown on an open web map
>
> **So that** I can decide where to go without interpreting a mandatory route.

**Acceptance criteria:**

> **Given** that an experience has participant coordinates
>
> **When** its map loads
>
> **Then** Leaflet renders approved OpenStreetMap-compatible data without drawing a required visit sequence.

**Verification:** Browser, attribution, and configuration test.

#### REQ-TEC-007 — Use the approved development and delivery tools

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** WS

**Description:** Development must use Laravel Sail with the application, PostgreSQL, mail, and local dependencies in containers. PHP, Composer, Node, test, and quality commands must run through Sail. Production must use an independent root `Dockerfile`, and GitHub Actions must run the approved checks in containers and verify the production build.

**User Story**

> **As** a MAINTAINER
>
> **I want** reproducible development, test, and delivery tools
>
> **So that** local and continuous-integration evidence use the same approved baseline.

**Acceptance criteria:**

> **Given** a host with Docker and the repository checkout
>
> **When** local or continuous-integration checks run
>
> **Then** Sail provides the application, PostgreSQL, mail, and required dependencies, and runs PHP tests, Pint, Larastan, npm, and Playwright without requiring PHP, Composer, Node, or PostgreSQL on the host.

> **Given** that a change enters continuous integration
>
> **When** the deployable artifact is verified
>
> **Then** GitHub Actions builds the independent production image from the root `Dockerfile` successfully.

**Verification:** Sail execution on a Docker-only host, CI execution in containers, and clean production-image build.

#### REQ-TEC-008 — Keep AI outside product runtime

**Priority:** Must | **Type:** Technical constraint | **Module:** Technology | **Source:** RD

**Description:** Artificial intelligence can assist development and review but must not run inside the product.

**User Story**

> **As** a PRODUCT OWNER
>
> **I want** artificial intelligence limited to the development workflow
>
> **So that** MVP01 has no runtime AI cost, dependency, or automated product decision.

**Acceptance criteria:**

> **Given** that an MVP01 user request is processed
>
> **When** runtime dependencies and network calls are inspected
>
> **Then** no artificial-intelligence service is required or invoked by the product.

**Verification:** Dependency, configuration, and runtime inspection.

## MVP01 exclusions

The following items have MoSCoW status **Won't for MVP01**. They remain outside the 69 implementable requirements:

- Billing, subscriptions, checkout, and payment processing.
- Apple Wallet and native applications.
- Visitor accounts and cross-device identity recovery.
- Reusable full business accounts.
- POS, sales, inventory, reservation, ordering, or delivery integration.
- Public participant applications, negotiation, or fee collection.
- Purchase points, multiple benefits, tiers, referrals, or promotional messaging.
- Automated recommendation, ranking, or runtime artificial intelligence.
- White-label operation or a general administration product.

## Product evolution

| Increment | Direction |
|---|---|
| MVP01 | Free production baseline with the complete experience flow. |
| MVP02 | Reusable organizer-business relationships, Apple Wallet, multilingual content, and richer analytics. |
| MVP03 | First-experience-free commercialization and optional paid services. |
| Release candidate | Load, restore, security, operational, legal, and migration evidence for the selected model. |

Later increments are directional. They require refinement before implementation.
