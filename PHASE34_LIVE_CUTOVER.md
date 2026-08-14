# NACS-Phil Phase 34 - Live Cutover

Do not change production DNS until this command succeeds on the prepared production environment:

```text
php artisan nacs:cutover-check --strict
```

It aggregates the existing production/host checks with the new Phase 30 data decision, Phase 32 physical browser acceptance, and Phase 33 restore verification.

## Final cutover order

1. Freeze content changes for the migration window.
2. Take a fresh database/private-file/public-media backup.
3. Verify the target database and approved import.
4. Verify the final production `.env` privately.
5. Confirm `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, secure sessions, production cache, and MySQL/MariaDB.
6. Configure the real Turnstile site key/secret and exact hostname.
7. Run migrations using the production release process.
8. Import only the Phase 30-approved data.
9. Verify storage permissions and public media.
10. Run `php artisan optimize`.
11. Run:
   - `php artisan nacs:host-check --strict`
   - `php artisan nacs:production-check --strict`
   - `php artisan nacs:data-audit --strict`
   - `php artisan nacs:acceptance-check --strict`
   - `php artisan nacs:recovery-check --strict`
   - `php artisan nacs:cutover-check --strict`
12. Change DNS only after all gates pass.
13. Verify SSL and HTTPS after DNS resolves.
14. Run the Phase 35 live HTTP verification.

## Rollback

If the live smoke test fails:

- stop additional content changes
- restore the prior application release
- restore the database only when the failed change affected data/schema and the rollback procedure requires it
- restore affected private/public files from the verified backup
- point traffic back to the known-good target when the hosting/DNS design supports it
- preserve logs for diagnosis without publishing private values

## External dependency

The repository cannot execute the actual DNS, SSL, hosting-control-panel, production-database, or Cloudflare-account operations without authorized access to those external systems. The cutover command is intentionally a gate, not an unsafe remote-control script.
