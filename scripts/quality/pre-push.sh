#!/usr/bin/env sh
set -eu

./scripts/quality/pre-commit.sh
./vendor/bin/sail npm run build
./vendor/bin/sail composer test
