# ADR-005: Use Laravel Sail for development and an independent production image

**Date:** 2026-09-02  
**Status:** Accepted

## Context

MVP01 needs one reproducible environment for a single developer, automated quality checks, and continuous integration without requiring PHP, Composer, Node, or PostgreSQL on the host. The production artifact has different security, size, process, and dependency requirements from the development environment.

## Options considered

1. Use Laravel Sail for development and CI quality checks, and maintain an independent root `Dockerfile` for production. This preserves Laravel-supported developer ergonomics while allowing a purpose-built production image, at the cost of maintaining two container definitions.
2. Use one custom Docker Compose topology and one image for development and production. This reduces the number of definitions but couples developer tooling and services to the production artifact.
3. Run language runtimes and services directly on developer and CI hosts. This reduces initial container configuration but creates environment drift and additional host prerequisites.

## Decision

Use Laravel Sail as the mandatory development runtime. The Sail topology contains the application, PostgreSQL, mail, and every local dependency required by the project. Run Artisan, Composer, npm, PHP tests, Pint, Larastan, and Playwright through Sail. Docker is the only required host dependency.

Use an independent `Dockerfile` at the repository root for production. CI runs the test and quality toolchain in containers, builds the production image, and verifies that build before the image can be promoted.

## Consequences

### Positive

- Local and CI checks use a reproducible container boundary.
- Developers do not need project language runtimes or services installed on the host.
- The production image can exclude development tools and use production-specific security and process configuration.
- CI detects production-image build failures before deployment.

### Negative

- Sail configuration and the production Dockerfile must be kept compatible with the same locked application dependencies.
- Container startup and filesystem performance can make local feedback slower on some hosts.
- Browser testing requires a container image or service with the supported Playwright browsers.

## Related requirements

- `REQ-TEC-001`, `REQ-TEC-002`, `REQ-TEC-003`, `REQ-TEC-005`, and `REQ-TEC-007`.
- `REQ-SEC-005`.

## References

- [Laravel Sail](https://laravel.com/docs/13.x/sail).
- [Docker build best practices](https://docs.docker.com/build/building/best-practices/).
