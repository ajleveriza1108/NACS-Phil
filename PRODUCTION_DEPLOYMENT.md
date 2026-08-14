# NACS-Phil Production Deployment Preparation — Phase 25

Phase 25 prepares the tested Laravel application for a real hosting environment. It does **not** contain a real domain, hosting password, database credential, SMTP credential, or production `APP_KEY`.

Current tested source baseline for this phase is recorded by Git history. Always deploy the exact tested commit or a later explicitly verified release commit.

## 1. Hosting requirements

Choose hosting that can run the current project requirements:

- PHP 8.3 or newer.
- Required PHP extensions for Laravel and the selected production database.
- MySQL/MariaDB or another database supported by the project.
- Composer access during deployment, or a deployment workflow that uploads the production `vendor` tree built from `composer.lock`.
- A writable Laravel `storage` directory and `bootstrap/cache`.
- HTTPS/SSL for the final domain.
- Ability to run Laravel Artisan commands.
- Persistent storage for `storage/app/private` and `storage/app/public`.
- A web document root pointed at Laravel's `public` directory, not the repository root.

The current Composer project requires PHP `^8.3` and Laravel `^13.8`.

## 2. Production environment

Use `PRODUCTION_ENV_TEMPLATE.txt` only as a guide. Create the real server `.env` privately.

Required production principles:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<final-domain>`
- generate the real `APP_KEY` on the production server
- use the real production database, not the local SQLite file
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` or stricter if verified compatible
- never commit the production `.env`

After setting the environment and deploying dependencies/build assets, run:

```bash
php artisan nacs:production-check --strict
```

Do not launch while that command reports a required blocker.

## 3. Database

Local development uses SQLite, but the project supports MySQL and MariaDB configuration through Laravel.

For normal shared hosting:

1. Create an empty MySQL/MariaDB database.
2. Create a dedicated database user.
3. Grant that user only the permissions required for this application database.
4. Put those credentials in the private production `.env`.
5. Back up the database before every production migration/update.
6. Run:

```bash
php artisan migrate --force
```

Do not upload `database/database.sqlite` as the production database.

## 4. Public and private storage

The project deliberately separates public and private files.

- `storage/app/public` — authorized public Gallery/media assets.
- `storage/app/private` — Laravel private/local storage.
- admissions documents must remain private and must never be copied into a public web directory.

For normal Laravel public media:

```bash
php artisan storage:link
```

If the hosting provider blocks symlinks, configure the host's documented equivalent rather than copying the entire private storage tree into `public`.

Persistent storage must survive code deployments.

## 5. Build and dependencies

On a server/build environment with Composer and Node available:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

If the host cannot run Node, build `public/build` in a trusted deployment environment from the locked dependencies and deploy that tested build artifact according to the host's workflow.

Do not run `npm install` casually on production when `npm ci` is available.

## 6. Laravel deployment sequence

Recommended release order:

```bash
php artisan down
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan nacs:production-check --strict
php artisan up
```

Adapt the Node/build and storage-link steps to the selected host.

If any required readiness check fails, keep maintenance mode active and repair the environment before reopening the site.

## 7. File permissions

The web/PHP user must be able to write only where Laravel needs runtime writes, especially:

- `storage`
- `bootstrap/cache`

Do not make the whole repository globally writable.

## 8. Backups

A production backup plan must include:

- database
- `storage/app/public`
- private admissions/runtime storage that must survive deployment
- any school-owned branding/media not recoverable from source control

Keep backup copies outside the live web root and preferably outside the hosting account itself.

Test restoring a backup before relying on it.

## 9. HTTPS and cookies

The final site must be HTTPS before enabling real family/admin use.

Verify:

- HTTP redirects to HTTPS.
- session cookies have the Secure and HttpOnly flags.
- Admin login and 2FA work after HTTPS enforcement.
- Admissions Apply/Track/Status/Receipt retain their private/no-store cache headers.

## 10. Facebook Live & Videos

Use a real public school Facebook recording and a real Facebook Live/replay during production verification.

Confirm:

- the preview/player loads on desktop and phone;
- Play works inside NACS-Phil;
- the Facebook fallback link opens correctly;
- the Facebook post remains Public and approved for embedding;
- no video binary is being uploaded to the NACS-Phil server.

## 11. Final launch gate

Before public launch, all three must be complete:

1. Phase 24 automated QA passed on the deployed release source.
2. `php artisan nacs:production-check --strict` passes on the real production server.
3. School Manager → Launch Readiness and the manual `FINAL_RELEASE_CHECKLIST.md` items are reviewed by the school.

Phase 25 prepares deployment. It does not by itself mean the school has approved the live launch.
