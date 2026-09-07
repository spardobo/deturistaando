#!/usr/bin/env sh
set -eu

readonly GITLEAKS_IMAGE='ghcr.io/gitleaks/gitleaks:v8.30.1@sha256:c00b6bd0aeb3071cbcb79009cb16a60dd9e0a7c60e2be9ab65d25e6bc8abbb7f'

repository_root=$(cd -- "$(dirname -- "$0")/../.." && pwd)

# Scan reachable Git history so a secret cannot be hidden by deleting it later.
docker run --rm \
    --volume "$repository_root:/repo:ro" \
    --workdir /repo \
    "$GITLEAKS_IMAGE" \
    git --no-banner --redact /repo
