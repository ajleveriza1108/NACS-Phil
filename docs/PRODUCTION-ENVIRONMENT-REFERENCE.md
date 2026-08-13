# NACS-Phil Production Environment Reference

This is a key/reference list only. Do not place real secrets in this file.

## Application

- `APP_NAME` - public/internal application name
- `APP_ENV=production`
- `APP_KEY` - generate and store privately; never commit
- `APP_DEBUG=false`
- `APP_URL=https://<final-domain>`

## Database

Use the production database supplied by the chosen host. Typical keys include:

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- database password supplied privately through the host/environment

Do not commit database credentials.

## Sessions and cache

Recommended production posture for this application:

- `SESSION_DRIVER=database` when supported
- `SESSION_LIFETIME=120` or an approved school policy value
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true` after HTTPS is active
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `CACHE_STORE=database` or another host-supported persistent cache

## Mail

Mail is optional until the school chooses to send messages from the website. If enabled, configure the production mail transport entirely through private environment/hosting settings.

## Filesystem

The application source must not contain runtime uploads. Public media and private school/admissions files belong in Laravel storage and require separate backup/restore handling.

## Production cache commands

After the production environment is complete, build Laravel production caches as part of deployment. Clear/rebuild them whenever environment or route configuration changes.

## 2FA and APP_KEY

Two-factor secrets are encrypted application data. Establish and protect the final production `APP_KEY` before enrolling production leadership accounts in 2FA. Changing the key later requires an intentional key-rotation/re-enrollment plan.
