# ADR-006: Use a lightweight CI runtime

**Date:** 2026-09-07
**Status:** Accepted

## Context

[ADR-005](005-sail-development-and-production-container.md) established Sail as the development runtime and an independent root `Dockerfile` as the production artifact. It also stated that CI quality checks run in containers and that CI builds the production image. The implemented quality workflow instead uses an ephemeral GitHub-hosted runner with explicitly configured PHP, Composer, and Node versions. Requiring Sail inside that already isolated runner would add container startup, downloads, and another orchestration layer without improving the evidence required by this project.

CI still needs isolated PostgreSQL and security tooling. Product browser journeys will also need the browser dependencies supplied by the pinned Playwright image when those journeys exist. The production image remains a separate deployable artifact whose automated build and verification have not yet been implemented.

## Options considered

1. Run PHP, Composer, and Node directly on the ephemeral GitHub-hosted runner, with containers only for justified services and tools. This keeps CI independent and efficient while preserving isolation where it adds value.
2. Start the complete Sail topology in GitHub Actions. This maximizes similarity with local development but adds startup cost, nested orchestration, and services such as Mailpit that CI does not require.
3. Run every CI dependency directly on the hosted runner. This reduces container use but weakens version and isolation boundaries for PostgreSQL, Gitleaks, and future browser dependencies.

## Decision

Laravel Sail is mandatory only for local development. GitHub Actions executes PHP, Composer, and Node commands directly on an ephemeral hosted runner with repository-approved versions and committed lockfiles.

CI uses containers selectively:

- PostgreSQL provides the disposable integration database.
- Gitleaks provides the pinned secret-scanning runtime.
- Playwright will provide pinned browser dependencies when complete product journeys enter the required gate.

The independent root `Dockerfile` remains the production artifact. A separate future delivery item must build and verify it in GitHub Actions without starting Sail.

This decision supersedes ADR-005 only where it requires quality checks to run inside containers. It does not supersede the accepted obligation to build and verify the independent production image in CI; that automation remains pending. ADR-005 remains authoritative for mandatory local Sail development and the independent production image.

## Consequences

### Positive

- CI avoids unnecessary Sail startup and duplicated orchestration.
- The hosted runner remains disposable and independently reconstructs dependencies from lockfiles.
- Services and specialized tools retain explicit, pinned container boundaries.
- The production-image obligation stays visible without being presented as a current gate.

### Negative

- Local and CI command wrappers differ even though they execute the same underlying tools.
- Approved PHP and Node versions must remain aligned between Sail, CI, and the production build stages.
- Selective container images and GitHub Actions setup steps require separate version maintenance.

## Related requirements

- `REQ-TEC-005` and `REQ-TEC-007`.
- `REQ-SEC-005`.

## References

- [ADR-005: Use Laravel Sail for development and an independent production image](005-sail-development-and-production-container.md).
- [Laravel Sail](https://laravel.com/docs/13.x/sail).
- [GitHub Actions service containers](https://docs.github.com/en/actions/tutorials/use-containerized-services/about-service-containers).
