#!/usr/bin/env bash
# NACS-Phil production update EXAMPLE.
# This is NOT wired to GitHub Actions. Adapt and verify it on staging first.

set -euo pipefail

EXPECTED_BRANCH="main"

current_branch="$(git branch --show-current)"
if [[ "$current_branch" != "$EXPECTED_BRANCH" ]]; then
    echo "ERROR: expected branch '$EXPECTED_BRANCH', found '$current_branch'." >&2
    exit 1
fi

if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
    echo "ERROR: tracked server source contains local edits. Refusing deployment." >&2
    git status --short --untracked-files=no
    exit 1
fi

git fetch origin main
git merge --ff-only origin/main

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build

php artisan optimize:clear

if [[ "${NACS_RUN_MIGRATIONS:-false}" == "true" ]]; then
    php artisan migrate --force
else
    echo "Migrations are disabled by default."
fi

php artisan optimize
php artisan nacs:production-check

echo "Source update completed. Run approved post-deploy/browser checks."
