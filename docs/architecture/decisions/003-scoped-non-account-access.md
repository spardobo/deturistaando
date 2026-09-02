# ADR-003: Use organizer accounts and scoped non-account access

**Date:** 2026-09-01  
**Status:** Accepted

## Context

Conventional accounts for every visitor and participating business create setup and return friction. The product still needs secure organizer ownership, business authorization, revocation, audit, and private visitor state.

## Options considered

1. Give organizers, businesses, and visitors conventional accounts.
2. Use only shared public links and QR codes.
3. Use organizer accounts, scoped business device access, and anonymous visitor credentials.

## Decision

Use Laravel accounts for organizers. Give each participating business one revocable experience-scoped device access with a PIN. Give each visitor one anonymous participation with separate private-view and validation credentials.

## Consequences

### Positive

- Visitors avoid registration and application installation.
- Businesses avoid complete account administration.
- Organizer ownership remains explicit.
- Business and visitor credentials remain limited to their required operations.

### Negative

- Business recovery depends on organizer reactivation.
- MVP01 does not reuse business identity across experiences.
- Anonymous participation cannot provide safe identity-based recovery.
- Shared visitor credentials remain a residual abuse risk.

## Related requirements

- `REQ-ORG-001` and `REQ-ORG-002`.
- `REQ-BUS-001` through `REQ-BUS-006`.
- `REQ-PAR-001`, `REQ-PAR-006`, and `REQ-SEC-001` through `REQ-SEC-004`.
