---
name: deturistaando-delivery-flow
description: "Trigger: DeTuristaAndo delivery status, start next item, Project transition, delivery flow. Orchestrate guarded Project transitions with the local CLI."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Activation Contract
Use for DeTuristaAndo Project #2 delivery transitions and `start-next`; do not use for generic Project administration.

## Hard Rules
- The local delivery CLI is the sole authority for Project reads and mutations. Never compose direct `gh project` transitions or DraftIssue conversion.
- Obtain separate human authorization for Project mutation, branch, commit, push, PR, merge, and Done. A dry-run authorizes none.
- Load only a coherent near-term DraftIssue wave into Backlog after a human-approved dry-run; do not create speculative waves.
- `start-next` conversion retains the Project item and creates its durable repository issue. Do not create or link a separate issue afterward.
- Local policy overrides generic guidance: use `Refs #<issue>` for intermediate PR work; use `Closes #<issue>` only in the final merged PR.

## Decision Gates
| Situation | Action |
| --- | --- |
| Candidate is ambiguous, blocked, or WIP is active | Stop and ask; do not mutate. |
| `start-next` conversion succeeded | Do not invoke `gentle-ai-issue-creation`; use the returned issue. |
| Implementation work | Hand off to `gentle-ai-work-unit-commits` only for commits, after commit authorization. |
| PR stage | Hand off to `gentle-ai-branch-pr` only for branch/PR work, after respective authorization. |
| User requests stacked PRs or change risks >400 lines | Hand off to `gentle-ai-chained-pr`. |
| Ready, Active, or Done transition | Require Project-mutation authorization and `--confirm-human-gate`; Done additionally requires acceptance evidence. |

## Execution Steps
1. For a normal status transition, run `npm run delivery:status -- --issue <N> --from <FROM> --to <TO>`; report its evidence. After explicit Project-mutation authorization, run `npm run delivery:status -- --issue <N> --from <FROM> --to <TO> --apply --confirm-human-gate`. Use only supported sequence transitions: Backlog→Ready→Active→Review→Verify→Done. Supply `--pr <N>` for Active→Review and Review→Verify.
2. For next work, run `npm run delivery:start-next -- --item <PROJECT_ITEM_ID> --wave "Wave N"`; report the selected item and checks. After explicit Project-mutation authorization, run `npm run delivery:start-next -- --item <PROJECT_ITEM_ID> --wave "Wave N" --apply --confirm-human-gate`; use its conversion and readback.
3. Read `docs/development/workflow.md` only on demand for DoR/DoD, broad policy, or ambiguity; do not read it for routine transitions.

## Output Contract
Return dry-run evidence, exact authorization received, apply readback, any handoff, and unresolved blockers or risks.

## References
- `../../docs/development/workflow.md` (on demand only)
