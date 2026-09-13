---
name: deturistaando-delivery-flow
description: "Trigger: DeTuristaAndo delivery status, Project transition, delivery flow. Orchestrate guarded Project transitions with the local CLI."
license: Apache-2.0
metadata:
  author: gentleman-programming
  version: "1.0"
---

## Activation Contract
Use for DeTuristaAndo Project #2 transitions, not generic Project administration.
## Hard Rules
- Keep authorization for Git, PR, merge, and Project mutation separate.
- Delegate to `gentle-ai-issue-creation`, `gentle-ai-branch-pr`, `gentle-ai-work-unit-commits`, and `gentle-ai-chained-pr`; use `npm run delivery:status -- ...` rather than direct Project mutation.
## Decision Gates
| Situation | Action |
| --- | --- |
| Ready, Active, or Done apply | Require human authorization and `--confirm-human-gate`. |
| Review or Verify | Supply CLI-required PR evidence. |
| Blocked or rejected check | Keep Blocked manual or stop. |

## Execution Steps
Run dry-run, review evidence, obtain authorization for `--apply`, then report mode, transition, readback, and blockers.
## Output Contract
Return evidence, authorization, and unresolved risk.

## References
- `../../docs/development/workflow.md`