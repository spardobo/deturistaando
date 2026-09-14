---
name: deturistaando-delivery-flow
description: "Trigger: DeTuristaAndo delivery flow, Project status, waves, PR handoffs, or next work selection."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "2.0"
---

## Activation Contract
Use for DeTuristaAndo delivery flow, GitHub Project movement, PR handoffs, and rolling-wave planning. Do not use for generic Project administration.

## Normal Board Flow
Use this Lean sequence:

`Backlog → Active → Review → Verify → Done`

- `Backlog`: scoped work available for the current or next coherent wave.
- `Active`: one primary item being implemented. Keep WIP at one.
- `Review`: the PR is open; code review, CI, and policy checks are addressed here.
- `Verify`: the PR is merged to `main`; integrated verification is underway or recorded.
- `Done`: the human accepted the result and sufficient evidence exists.

`Ready` is not part of the normal flow. If an old Project item still uses it, treat it as transitional legacy state and ask before moving it.

## Routine Playbook
1. Select one clear Backlog item from the current wave; ask if WIP is already Active or selection is ambiguous.
2. When starting a Project DraftIssue, convert that same Project item into a repository issue before moving it to Active. Do not create a duplicate issue and link it later.
3. If the Backlog item is already a repository issue, use that issue directly.
4. Request explicit human authorization before Project conversion/status movement, branch creation, commit, push, PR creation, merge, and Done/acceptance.
5. During Active, run the smallest checks that prove the changed behavior. Do not repeat broad checks unless code changed or a failure explains why.
6. Move to Review when a focused PR exists and references the issue.
7. Move to Verify only after the final PR is merged to `main`.
8. Move to Done only after integrated verification and human acceptance.

## Waves and Lazy Loading
Waves are rolling planning horizons, not extra ceremony.

- Keep Backlog limited to the current coherent wave and the immediate next work needed to maintain flow.
- When every item in a wave is Done, plan/load the next wave from `docs/requirements.md`.
- Do not eagerly read broad documentation during routine board or PR operations.
- Lazy-load `docs/requirements.md` only for wave completion, next-wave planning, ambiguity, scope validation, or acceptance questions.
- Lazy-load `docs/development/workflow.md`, architecture decisions, quality strategy, or requirement detail only when the current risk or decision needs them.
- Do not create speculative distant waves.

## PR and Merge Rules
- Keep PRs small, focused, and tied to one outcome.
- Use `Refs #<issue>` for intermediate PRs in an intentional chain.
- Use `Closes #<issue>`, `Fixes #<issue>`, or `Resolves #<issue>` for the final PR.
- If the work risks exceeding a comfortable review size, split before opening the PR.
- Review and Verify remain distinct: open PR versus merged integrated result.

## Stop Conditions
Stop and ask instead of inferring when:

- scope, requirement traceability, or acceptance is unclear;
- WIP is already Active;
- CI fails without an obvious local fix;
- merge conflicts, provider access, security, data migration, or deployment risk appears;
- Project state disagrees with issue or PR state;
- the next wave cannot be derived cleanly from requirements.

## Output Contract
Report only what matters: current state, evidence observed, human authorization received, next action, and unresolved blockers or risks.
