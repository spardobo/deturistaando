# ADR-001: Use a modular monolith with hexagonal architecture

**Date:** 2026-09-01  
**Status:** Accepted

## Context

**DeTuristaAndo** needs one production web application and one small delivery workflow. Visit, progress, entitlement, redemption, and provider behavior need explicit boundaries. The project must avoid both a coupled Laravel codebase and a ceremonial layer for every CRUD operation.

## Options considered

1. Use one conventional Laravel application without module boundaries.
2. Use a modular monolith with hexagonal architecture.
3. Use independently deployed services.

## Decision

Use one modular Laravel monolith with hexagonal architecture. Organize business behavior by module. Separate domain, application, and adapters. Keep dependencies directed toward the domain.

Apply the structure according to current risk. Critical rules remain independent from Laravel presentation, Eloquent, and provider SDKs. Simple reads and routine persistence can use Laravel conventions in adapters. Create ports, services, and repositories only when they protect a current use case or dependency boundary.

Apply DRY, KISS, YAGNI, and SOLID as design criteria. Do not use them to justify speculative interfaces, generic repositories, or empty layers.

## Consequences

### Positive

- One deployment and database reduce delivery and operational cost.
- Module ownership limits coupling inside the monolith.
- Database transactions protect visit, entitlement, and redemption consistency.
- Hexagonal dependency rules keep critical behavior testable.
- External providers can change without entering the domain model.

### Negative

- Module and dependency rules require architecture tests and review discipline.
- Some critical modules need mapping between domain objects and Eloquent records.
- Modules cannot deploy independently.
- The team must justify pattern use instead of applying one structure mechanically.

## Related requirements

- `REQ-MNT-001` through `REQ-MNT-004`.
- `REQ-TEC-001` through `REQ-TEC-005`.
