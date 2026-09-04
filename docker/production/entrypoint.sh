#!/bin/sh

# Stop immediately on failures and reject unset variables during startup.
set -eu

# Cache Laravel configuration, events, routes, and views only after deployment
# variables are available; build-time caching would freeze an empty environment.
php artisan optimize --no-ansi

# Replace the shell so Supervisor receives signals directly as container PID 1.
exec "$@"
