#!/usr/bin/env sh
set -eu

./vendor/bin/sail composer quality:quick
./vendor/bin/sail npm run quality:local
