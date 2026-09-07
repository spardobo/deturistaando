#!/usr/bin/env sh
set -eu

./scripts/quality/pre-commit.sh
./scripts/quality/dependency-audit.sh
./scripts/quality/secret-scan.sh
./vendor/bin/sail npm run build
./vendor/bin/sail composer test
