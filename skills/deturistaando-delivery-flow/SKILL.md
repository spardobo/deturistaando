---
name: deturistaando-delivery-flow
description: "Trigger: DeTuristaAndo delivery status, start next item, Project transition, delivery flow. Orchestrate guarded Project transitions with the local CLI."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Activation Contract
Use for DeTuristaAndo Project #2 transitions, not generic Project administration.
## Hard Rules
- Keep authorization for Git, PR, merge, and Project mutation separate.
- Delegate to `gentle-ai-issue-creation`, `gentle-ai-branch-pr`, `gentle-ai-work-unit-commits`, and `gentle-ai-chained-pr`; use the local delivery CLI rather than direct Project mutation.
- Load only coherent near-term wave DraftIssues into Backlog after a human-approved dry-run; never create distant speculative waves.
- Conversion keeps the same Project item and creates its durable repository issue; never create and link an independent issue later.
## Decision Gates
| Situation | Action |
| --- | --- |
| Work the next item | Inspect current wave, dependencies, Project order, blockers, and WIP; recommend one candidate only when unambiguous, otherwise ask. |
| Start next apply | Show exact item and require human confirmation before `delivery:start-next --apply --confirm-human-gate`. |
| Ready, Active, or Done apply | Require human authorization and `--confirm-human-gate`. |
| Review or Verify | Supply CLI-required PR evidence. |
| Blocked or rejected check | Keep Blocked manual or stop. |

## Execution Steps
Run dry-run, review evidence, obtain authorization for `--apply`, then report mode, transition, readback, and blockers. After start-next succeeds, hand off separately to the issue, branch, commit, and PR skills; branch, commit, push, PR, and merge still require explicit authorization.
## Output Contract
Return evidence, authorization, and unresolved risk.

## References
- `../../docs/development/workflow.md`