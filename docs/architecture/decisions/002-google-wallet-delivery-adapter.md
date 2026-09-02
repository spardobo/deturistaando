# ADR-002: Keep Google Wallet outside the core domain

**Date:** 2026-09-01  
**Status:** Accepted

## Context

Google Wallet is mandatory in MVP01. The provider controls issuer approval, object models, update behavior, and external availability. A provider failure must not change a valid participation, visit, benefit, or redemption.

## Options considered

1. Use Google Wallet models throughout the application.
2. Make the web card optional and treat Google Wallet as the source of truth.
3. Keep server state authoritative and map it through a Wallet adapter.

## Decision

Store authoritative participation state in PostgreSQL. Use a provider-neutral `WalletPassGateway` and a Google Generic Pass adapter. Keep a complete private web view as the operational fallback.

## Consequences

### Positive

- Provider failure cannot corrupt domain state.
- Google Wallet remains part of the complete visitor journey.
- Apple Wallet or another provider can be added later without changing domain rules.
- Contract tests can verify provider mapping and error behavior.

### Negative

- The application must maintain synchronization state and retries.
- The web and Wallet representations can be temporarily inconsistent.
- Provider approval and production credentials remain external release dependencies.

## Related requirements

- `REQ-PAR-003` through `REQ-PAR-005`.
- `REQ-REL-001`.
- `REQ-MNT-003`.

## References

- [Google Wallet Generic Pass](https://developers.google.com/wallet/generic).
- [Issue passes through web links](https://developers.google.com/wallet/generic/web).
