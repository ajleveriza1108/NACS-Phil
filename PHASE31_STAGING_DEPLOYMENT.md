# NACS-Phil Phase 31 - Hosting and Staging Deployment

The repository is prepared for provider-specific staging without hardcoding a hosting company, domain, database password, or control-panel path.

## Required staging shape

- document root points to Laravel `public`
- PHP 8.3 or newer
- MySQL or MariaDB
- writable Laravel storage and bootstrap cache
- production Vite build deployed
- HTTPS staging hostname
- `APP_DEBUG=false`
- secure encrypted server-side sessions
- persistent cache
- private admissions files outside public web storage
- Turnstile configured for the staging hostname

Run on the staging host:

```text
php artisan nacs:host-check --strict
php artisan nacs:staging-check --strict
php artisan nacs:production-check
```

The production check is useful on staging but may intentionally report the environment name as not production.

## Safe deployment order

1. Create the staging database and database user in the host control panel.
2. Deploy source without `.env`, local SQLite, private uploads, `.nacs-backups`, or local acceptance records.
3. Create the server `.env` privately.
4. Generate/retain the server `APP_KEY`.
5. Set the staging HTTPS `APP_URL`.
6. Configure MySQL/MariaDB.
7. Run migrations on the staging database only after a backup/rollback point exists.
8. Import only the approved data selected in Phase 30.
9. Configure public storage and private admissions storage.
10. Deploy `public/build`.
11. Configure Turnstile for the staging hostname.
12. Clear caches, then run the staging checks.
13. Do not point the public production domain at staging yet.

## External dependency

Actual staging deployment cannot be performed from the repository alone. It requires the chosen hosting account, staging hostname, database credentials, SSL, and server access. The codebase deliberately refuses to invent those values.
