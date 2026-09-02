# **DeTuristaAndo** Documentation Standard

This document defines how the **DeTuristaAndo** documentation is organized and maintained. Each document is a living source with one stable purpose.

## Principles

| Principle | Project rule |
|---|---|
| Single source | Put each rule or decision in one authoritative document. Link instead of copying. |
| Progressive disclosure | Explain product, domain, requirements, architecture, and delivery in that order. |
| Rolling wave | Detail the active delivery slice. Keep later work at roadmap level. |
| Evidence | Verify code, integrations, deployment, and external claims before stating them as facts. |
| Proportionality | Add process, abstraction, security, or tooling only when product risk or delivery needs justify it. |
| Lean scope | Remove information that does not help a current decision, implementation, operation, or review. |

## Language and terminology

- Write `README.md` in neutral professional Spanish.
- Write technical documents in English.
- Write code, identifiers, requirement IDs, ADR IDs, and configuration names in English.
- Write the product name as **DeTuristaAndo**.
- Use **experience** as the domain term. Use **experiencia** in Spanish.
- Keep official framework, standard, command, and API names.

## Writing profile

Technical documents use controlled English inspired by ASD-STE100. The project does not claim formal certification.

- Use short, direct sentences and active voice.
- Put one main fact or instruction in each sentence.
- Use one term for one concept.
- Define domain terms before extensive use.
- Put a condition before the action that it controls.
- Prefer lists for procedures and tables for exact comparisons.
- Use `MUST`, `MUST NOT`, `SHOULD`, and `MAY` only for normative requirements.
- Avoid decorative language and vague terms such as *fast*, *robust*, or *secure* without a criterion.
- Do not restate framework defaults unless the product changes, verifies, or depends on them.
- Do not repeat context that a linked upstream document already explains.

## Authoritative documents

| Document | Responsibility |
|---|---|
| [`../README.md`](../README.md) | Product entry point, main flow, MVP01 boundary, stack, and repository status. |
| [`market-analysis.md`](market-analysis.md) | Market evidence, alternatives, positioning, adoption barriers, and commercial direction. |
| [`conceptual-design.md`](conceptual-design.md) | Actors, domain language, lifecycles, invariants, and main workflows. |
| [`ui-ux-guidelines.md`](ui-ux-guidelines.md) | Interaction model, screen intent, visual identity, accessibility, and abandonment controls. |
| [`requirements.md`](requirements.md) | Executive requirement register, User Stories, Given-When-Then acceptance, verification, and MVP01 technical constraints. |
| [`architecture/overview.md`](architecture/overview.md) | System boundaries, modules, integrations, data, and deployment baseline. |
| [`architecture/security.md`](architecture/security.md) | Assets, threats, access rules, product-specific controls, and verification. |
| `architecture/decisions/NNN-title.md` | One durable architecture decision and its consequences. |
| [`quality-strategy.md`](quality-strategy.md) | Test scope, measurable quality targets, CI gates, and release evidence. |
| [`development/workflow.md`](development/workflow.md) | Lean/Kanban flow, Git rules, work-item policy, and delivery metrics. |

## Reading and change order

Use the document order in the README. A downstream document must not introduce a product concept that is absent from its upstream sources.

When behavior changes:

1. Update the product or domain source that owns the change.
2. Update requirements if observable behavior changes.
3. Update architecture, security, or an ADR if implementation boundaries change.
4. Update quality and workflow only when verification or delivery changes.
5. Update the README only when its public summary, MVP01 boundary, stack, or executable setup changes.

## Information state

| State | Meaning |
|---|---|
| Conceptual | Agreed product or domain behavior that does not assert code exists. |
| Planned | Accepted implementation or operational direction. |
| Implemented | Verified in source code or a working environment. |
| Pending | Unresolved information that blocks current work. |

Use a state label only when the text can imply more certainty than evidence supports. Git history provides document versioning.

## Detail policy

- Keep requirements at outcome level. Put field rules and exhaustive acceptance variants in the active work item or tests.
- Keep architecture at module and boundary level. Let Laravel conventions resolve routine class and folder choices.
- Document design principles and patterns as decision criteria. Keep class-level application in the active work item or code.
- Document framework behavior only when the project configures or relies on it.
- Add an ADR only for a costly, risky, or cross-cutting decision with credible alternatives.
- Keep an ADR close to one page. Supersede it instead of rewriting accepted history.
- Use Mermaid only when state, sequence, or structure is clearer than prose.
- Delete obsolete text instead of preserving it as commentary.

## Requirement format

Start `requirements.md` with the total count, MoSCoW distribution, type distribution, and a searchable register with these columns: sequence, ID, priority, module, type, and description.

Each requirement contains:

1. A stable domain-based ID and concise title.
2. Priority, type, module, and source.
3. One outcome-focused description.
4. One User Story in As-I want-So that form.
5. One or more Given-When-Then acceptance scenarios.
6. A verification method.

Use one acceptance scenario when it proves the requirement. Add another scenario only for a material rejection, failure, retry, or concurrency path. Add a MoSCoW rationale for Should and Could requirements or for a Must priority that is not evident from risk.

Findings, affected files, and solution hints are optional sections for verified defects, refactoring, or implemented constraints. Do not add these sections to greenfield product requirements without code evidence.

## Evidence and metrics

- Prefer primary sources for external technical and market claims.
- Record a verification date for time-sensitive market evidence.
- Treat vendor impact claims as vendor claims.
- Keep research screenshots under `docs/evidence/` and explain their origin.
- Use metrics only when they drive a product or delivery decision.
- Record a baseline before setting a project-specific improvement target.
- Treat metrics as signals. Do not optimize a number at the expense of product behavior.

## Validation

Before merging a documentation change:

- Confirm that the content belongs to the document.
- Check terminology, language, headings, lists, tables, code fences, and Mermaid syntax.
- Check relative links, anchors, requirement IDs, and ADR references.
- Search for inherited names and obsolete decisions.
- Compare implementation claims with repository evidence.
- Compare external claims with cited primary sources.
- Confirm that no deleted document is referenced.
- Confirm that the README remains an entry point and not an implementation inventory.

Automate deterministic checks when repository infrastructure exists. Do not claim automation before its workflow passes.
