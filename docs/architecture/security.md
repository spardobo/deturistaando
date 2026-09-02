# **DeTuristaAndo** Security Architecture

This document applies security controls to the [conceptual design](../conceptual-design.md) and [MVP01 requirements](../requirements.md). Controls are proportional to product risk and reuse Laravel defaults where they are suitable.

## Approach

- Use framework protections before custom security code.
- Add custom controls only for product-specific trust boundaries.
- Deny protected actions by default.
- Collect no visitor identity when the workflow does not need it.
- Protect state changes more strongly than public discovery.
- Automate inexpensive checks and keep manual review focused on access and concurrency.

Security work must reduce a credible risk. MVP01 does not add enterprise controls, infrastructure, or ceremonies without a concrete asset or threat.

## Assets and trust boundaries

| Asset | Main risk | Boundary |
|---|---|---|
| Organizer account | Account takeover or unsafe OAuth linking. | Public browser to Fortify and Socialite session. |
| Business invitation and device access | Reuse, guessing, sharing, or lost device. | Untrusted message link to limited business context. |
| Private participation | Unauthorized access or credential disclosure. | Visitor-held opaque credentials to private view and validator. |
| Visit and progress | Fraud, duplicate mutation, or cross-experience use. | Business confirmation to domain transaction. |
| Entitlement and redemption | Over-allocation or repeated redemption. | Authorized redemption point to locked transaction. |
| Provider secrets | Source or log disclosure. | Application runtime to external provider. |
| Audit evidence | Tampering or excessive sensitive detail. | Domain operation to append-only operational record. |

Public experience content is not confidential. Organizer email, private credentials, device state, failed-attempt context, and audit data are protected.

## Access rules

| Capability | Public | Visitor credential | Business access | Organizer |
|---|---:|---:|---:|---:|
| Read published experience | Yes | Yes | Yes | Yes |
| Read private participation | No | Own participation | Temporary validation context | No by default |
| Confirm visit | No | No | Own active participant | No |
| Redeem benefit | No | No | Authorized participant only | No |
| Configure experience | No | No | No | Owned experience |
| Manage business access | No | No | No | Owned experience |
| View reports | No | No | Own participant | Owned experience |

Platform incident access is exceptional, audited, and limited to the information required for support or abuse handling.

## Relevant threats and controls

| Threat | Control |
|---|---|
| Organizer accesses another experience | Ownership policies and scoped queries. |
| OAuth callback links the wrong account | Socialite state validation, fixed callbacks, verified linking flow, and no automatic email-only linking. |
| Invitation is reused or leaked | High-entropy token, expiry, one-time consumption, and revocation. |
| PIN is guessed | Laravel rate limiting, generic failure response, cooldown, and audit signal. |
| Device token is copied | Secure cookie or protected storage, rotation, revocation, and experience scope. |
| Private URL leaks through logs or referrers | Separate credentials, no token logging, restrictive referrer policy, and no third-party scripts on private pages. |
| Public QR is used to fabricate visits | Public QR has no mutation authority; an active business confirms every visit. |
| Credential is used in another experience | Bind participation, experience, participant, and business access on the server. |
| Retry duplicates a visit or redemption | Idempotency key, unique constraints, and locked transaction. |
| Benefit capacity is exceeded | Atomic reservation and database constraint. |
| Uploaded media carries unsafe content | Type, size, dimensions, decode, storage, and delivery restrictions. |
| Dependency or image contains a known vulnerability | Locked dependencies, automated audits, and container scan. |

## Laravel security baseline

Use and verify Laravel and starter-kit support for:

- CSRF protection on browser mutations.
- Server-side validation and contextual output escaping.
- Parameterized database access through Eloquent or the query builder.
- Fortify authentication, password hashing, reset, email verification, and login throttling.
- Session rotation after authentication.
- Secure, HTTP-only, same-site cookies in production.
- Authorization policies for every protected resource and action.
- Generic authentication errors that do not enumerate accounts.

Do not duplicate these controls with custom middleware unless the product requires different behavior.

## Product-specific credentials

### Organizer

- Keep password and Google access paths available.
- Require an explicit, safe flow before linking identities.
- Revoke all active sessions after confirmed compromise.
- Consider two-factor authentication after MVP01 or earlier if organizer privileges expand.

### Business access

- Store invitation and device tokens as hashes when recovery is not required.
- Hash the PIN with Laravel's password hasher.
- Rate-limit activation and PIN attempts by credential context and network signal.
- Rotate device access after recovery and invalidate the prior token.
- Never turn limited business access into a general user session.

### Visitor

- Generate high-entropy opaque values with a cryptographically secure source.
- Use different values for private view and validation.
- Put no personal or sequential identifier in a QR payload.
- Expire access according to the experience retention rule.

## Transaction integrity

Visit confirmation, entitlement creation, capacity reservation, and redemption are server-side operations inside PostgreSQL transactions.

Each sensitive request includes an idempotency key. A repeated technical request returns the original result. It does not create a second event.

Audit evidence records actor scope, action, target, result, time, and correlation identifier. It does not store raw private credentials or unnecessary visitor information.

## Secrets and external providers

- Keep provider credentials outside source control and Docker images.
- Use environment-managed secrets with separate values per environment.
- Grant the minimum provider permissions.
- Rotate a secret after suspected disclosure.
- Do not expose provider error detail to users.
- Keep debug mode disabled in production.

## Privacy and retention

MVP01 does not request visitor name, email, phone, or demographic data. It stores an anonymous participation, visit history, acquisition source, and technical context required for integrity and operations.

Define retention before production deployment. Remove or aggregate expired participation and technical data when legal, operational, and fraud needs no longer justify it.

## Delivery controls

The minimum security gate includes:

- Authorization and abuse-case tests for each protected use case.
- Concurrency tests for visit, entitlement, capacity, and redemption.
- `composer audit` and `npm audit` or equivalent dependency review.
- Secret scanning and production configuration checks.
- Container vulnerability scan before release.
- Manual review of OAuth linking, private URLs, uploads, logging, and error responses.

Run a broader OWASP review before public release. Do not require a full penetration-testing program for every development increment.

## Incident baseline

The operator must be able to revoke organizer sessions, business access, and compromised provider credentials; suspend abusive content; identify affected actions from audit evidence; and restore service from a verified backup.

Residual risks include credential sharing, screenshots of a private QR, dishonest staff confirmation, limited free-tier availability, and organizer-defined benefit disputes. MVP01 reduces these risks but does not claim physical identity or purchase verification.

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/).
- [Laravel Fortify](https://laravel.com/docs/13.x/fortify).
- [Laravel authorization](https://laravel.com/docs/13.x/authorization).
