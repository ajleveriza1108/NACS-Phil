# Phase 40 - Production Launch

Production launch is intentionally host-specific and is **not** activated by the cumulative installer.

The source repository can be prepared and validated automatically. A live deployment cannot be completed truthfully until NACS has an actual hosting provider, production domain, DNS, credentials, backups, persistent storage, and approved manual acceptance.

## Required external inputs

Before cutover, NACS must provide or approve:

- hosting provider/platform and deployment access
- production domain and DNS
- HTTPS/TLS
- production database
- persistent public media storage
- persistent private admissions storage
- private student-document provider and credentials
- production mail configuration
- Turnstile production configuration
- persistent cache/session strategy
- backup location and tested rollback procedure
- uptime/error monitoring destination
- approved staging/browser acceptance

Never commit `.env`, credentials, database exports, private admissions/student files, logs, or backups.

## Production security baseline

The live host must use:

- `APP_ENV=production`
- `APP_DEBUG=false`
- final HTTPS `APP_URL`
- a production-only `APP_KEY`
- a non-SQLite production database
- encrypted HTTPS-only server-side sessions
- a persistent cache
- Turnstile enabled with the exact production hostname
- least-privilege database credentials
- unique staff accounts
- two-factor authentication for Super Admin and Principal accounts
- no shared administrator credentials

Student documents must keep `allow_local_fallback` disabled. Configure the approved private external document provider before real confidential student documents are accepted.

## Backup and recovery gate

Before migration or cutover:

1. Create a production database backup.
2. Back up private admissions and other persistent files.
3. Confirm public media recovery.
4. Keep at least one protected copy outside the live hosting account.
5. Perform a real database restore test.
6. Perform a private-file restore test.
7. Record only the result, never secrets or backup contents, in the ignored `.nacs-recovery-verification.json` file.
8. Run `php artisan nacs:recovery-check --strict`.

A backup that has never been restored is not a proven recovery plan.

## Safe cutover sequence

1. Freeze the approved release commit and confirm a clean `main`.
2. Back up the current production database and persistent files.
3. Deploy the exact approved `main` commit.
4. Install PHP dependencies from the lockfile using production/no-dev options appropriate to the host.
5. Build or deploy the exact approved Vite assets.
6. Review every pending migration before execution.
7. Run migrations only after backup and rollback points are confirmed.
8. Rebuild Laravel caches appropriate to the host.
9. Verify persistent public and private storage.
10. Verify production mail without sending sensitive student information.
11. Verify `/up` returns a healthy response without exposing secrets.
12. Run `php artisan nacs:production-check --strict`.
13. Run `php artisan nacs:host-check --strict`.
14. Run `php artisan nacs:data-audit --strict`.
15. Run `php artisan nacs:acceptance-check --strict`.
16. Run `php artisan nacs:recovery-check --strict`.
17. Run `php artisan nacs:cutover-check --strict`.
18. Run `php artisan nacs:go-live-check --strict`.
19. Smoke-test the public website, admin, admissions, SIS, and Student & Parent Portal.
20. Keep the previous release/rollback point available until acceptance is complete.

## Monitoring after launch

Configure an external uptime monitor against `/up` and monitor application/server logs without exposing them publicly. During the first launch window, watch:

- HTTP 5xx errors
- failed login spikes and lockouts
- Turnstile failures
- mail delivery failures
- database/storage errors
- failed uploads/downloads
- queue/scheduler failures if enabled
- disk and database capacity
- certificate expiration

The branded 404, 419, 429, 500, and 503 pages are user-facing fallbacks, not a substitute for server monitoring.

## Privacy operations

Before opening the SIS to real families, NACS should approve:

- who may create, view, edit, export, and delete student records
- retention periods for admissions, grades, attendance, finance, guardians, and uploaded documents
- account deactivation procedures
- incident-response and breach-escalation contacts
- backup retention and deletion rules
- photo/media approval and consent procedure

An independent vulnerability assessment or penetration test is strongly recommended before broad SIS use with real family data.

## Automation policy

`.github/deployment-templates/production-deploy.yml` remains an inactive template. Do not activate automatic production deployment until the actual host, secrets, persistent paths, migration policy, health checks, monitoring, and rollback behavior are configured and reviewed.

Phase 40 is complete only after the real host cutover succeeds. Source preparation alone must never be reported as a live deployment.
