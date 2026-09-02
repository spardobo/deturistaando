# ADR-004: Use the official Laravel Livewire application stack

**Date:** 2026-09-01  
**Status:** Accepted

## Context

MVP01 needs organizer authentication, server-rendered public pages, reactive forms, a mobile validator, and a maintainable deployment. A separate frontend application would duplicate contracts and delivery work.

## Options considered

1. Use the official Laravel Livewire starter kit.
2. Use the official React starter kit with Inertia.
3. Build a separate SPA and Laravel API.
4. Build authentication and UI foundations without a starter kit.

## Decision

Use PHP 8.4, Laravel 13, and the official Livewire starter kit. Use Fortify, Socialite, Blade, Livewire 4, Alpine.js, Tailwind CSS 4, Flux UI Free, and PostgreSQL 16.

Google is the initial social provider. Email credentials remain available.

## Consequences

### Positive

- The project reuses maintained authentication and account flows.
- One application owns presentation and domain delivery.
- Livewire supports reactive workflows without a separate SPA contract.
- Flux and Tailwind provide consistent, customizable UI primitives.
- PostgreSQL supports transactional and concurrency-sensitive rules.

### Negative

- A future independent frontend requires a new delivery adapter.
- Livewire components need review to prevent domain logic from entering presentation code.
- Social account linking still needs explicit takeover protection.

## Related requirements

- `REQ-ORG-001` and `REQ-ORG-002`.
- `REQ-TEC-001` through `REQ-TEC-005`.
- `REQ-MNT-001` through `REQ-MNT-004`.

## References

- [Laravel starter kits](https://laravel.com/docs/13.x/starter-kits).
- [Laravel Fortify](https://laravel.com/docs/13.x/fortify).
- [Laravel Socialite](https://laravel.com/docs/13.x/socialite).
