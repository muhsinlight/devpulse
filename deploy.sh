#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

compose() {
    docker compose "$@"
}

if ! docker compose version >/dev/null 2>&1; then
    echo "Docker Compose is required. Install Docker Engine and the Compose plugin." >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Missing .env. Copy docker/.env.example to .env and fill in production values, including APP_KEY." >&2
    exit 1
fi

if ! grep -qE '^APP_KEY=base64:.+' .env; then
    echo "APP_KEY is missing. Generate one with: php artisan key:generate --show" >&2
    echo "Then set APP_KEY in .env before running this script." >&2
    exit 1
fi

if ! grep -qE '^DB_PASSWORD=.+' .env; then
    echo "DB_PASSWORD is missing in .env." >&2
    exit 1
fi

pull=1
if [[ "${1:-}" == "--no-pull" ]]; then
    pull=0
fi

if [[ "$pull" -eq 1 ]]; then
    if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        git pull --ff-only
    fi
fi

compose build app nginx
compose up -d --remove-orphans --wait
compose exec -T app php artisan migrate --force

echo "Deploy finished. Check GET /up on APP_URL."
