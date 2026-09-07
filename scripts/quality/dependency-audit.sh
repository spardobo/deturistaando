#!/usr/bin/env sh
set -eu

# Composer fails for every known advisory and for abandoned locked packages.
./vendor/bin/sail composer audit --locked --no-interaction --abandoned=fail

# npm reports every finding but blocks the push only for high or critical risk.
./vendor/bin/sail npm audit --audit-level=high
