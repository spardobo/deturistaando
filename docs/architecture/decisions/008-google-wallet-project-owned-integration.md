# ADR-008: Use a project-owned Google Wallet Integration

**Date:** 2026-09-11
**Status:** Accepted
**Supersedes:** [ADR-002: Keep Google Wallet outside the core domain](002-google-wallet-delivery-adapter.md)

## Context

Google Wallet remains required for the MVP01 journey, but provider availability, issuer approval, and SDK behavior cannot decide product state. ADR-002 remains an unchanged historical record; its mandatory provider-neutral `WalletPassGateway` conflicts with the selected project-owned Integration default.

## Options considered

1. Retain ADR-002's mandatory provider-neutral gateway and Google-specific adapter.
2. Use Google Wallet SDK models throughout product code.
3. Use a project-owned Google Wallet Integration, with a contract only for a documented current need.

## Decision

Use a project-owned Google Wallet Integration as the default location for Google SDK calls, request and response mapping, credentials interpretation, and retryable or terminal provider-error classification.

PostgreSQL remains authoritative for participation, visit, benefit, redemption, Wallet mapping, and synchronization state. Commit that local state before dispatching dependent Wallet synchronization. Persist synchronization attempts and retry retryable failures after commit. A Wallet delivery failure must not invalidate participation, visit, benefit, redemption, or the complete private web experience.

Google Wallet remains part of the visitor journey: the Integration creates or updates the Wallet representation and its private-pass link after authoritative state commits. The private web experience remains fully usable when Wallet delivery fails.

## Contract rule

Introduce a provider-neutral Wallet contract only when a documented current substitution, test, or boundary need demonstrates value. Do not require `WalletPassGateway`, a port, adapter, interface, or contract by default.

## Consequences

### Positive

- Provider-specific mapping and error handling stay isolated in one project-owned Integration.
- PostgreSQL authority, private-web fallback, and retryable synchronization protect the product journey.
- The default avoids speculative abstraction while retaining a documented escape path for a real boundary.

### Negative

- Post-commit jobs, listeners, or Actions may depend on the concrete Integration until a current contract need exists.
- Wallet and private-web representations can be temporarily inconsistent while retryable synchronization is pending.
- Provider approval and production credentials remain external release dependencies.

## Related requirements

- `REQ-PAR-003` through `REQ-PAR-005`.
- `REQ-REL-001`.
- `REQ-MNT-003`.

## References

- [Google Wallet Generic Pass](https://developers.google.com/wallet/generic).
- [Issue passes through web links](https://developers.google.com/wallet/generic/web).
