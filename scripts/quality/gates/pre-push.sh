#!/usr/bin/env sh
set -eu

# repeat the fast local baseline before publishing commits
./scripts/quality/gates/pre-commit.sh

# reject vulnerable dependencies and secrets in reachable Git history
./scripts/quality/security/audit-dependencies.sh
./scripts/quality/security/scan-git-secrets.sh

# verify production asset compilation
./vendor/bin/sail npm run build

# run integration tests
./vendor/bin/sail composer test:feature
