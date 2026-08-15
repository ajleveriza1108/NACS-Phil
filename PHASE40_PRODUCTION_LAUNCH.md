# Phase 40 — Production Launch

Production launch is intentionally host-specific and is **not** activated by the cumulative installer.

## Required external inputs

Before cutover, NACS must provide or approve:

- hosting provider/platform and deployment access
- production domain and DNS
- HTTPS/TLS
- production database
- persistent private storage
- production mail configuration
- Turnstile production configuration
- backup location and tested rollback procedure
- approved staging/browser acceptance

Never commit `.env`, credentials, database exports, private admissions/student files, logs, or backups.

## Safe cutover sequence

1. Back up the current production database and persistent files.
2. Deploy the exact approved `main` commit.
3. Install production dependencies from lockfiles.
4. Build or deploy the approved Vite assets.
5. Review migrations before running them.
6. Run migrations only after the backup/rollback point is confirmed.
7. Clear and rebuild Laravel caches appropriate to the host.
8. Verify persistent storage and mail.
9. Run production readiness/health checks.
10. Smoke-test the public website, admin, admissions, and portal.
11. Keep the previous release/rollback point available until acceptance is complete.

## Automation policy

`.github/deployment-templates/production-deploy.yml` remains an inactive template. Do not activate automatic production deployment until the actual host, secrets, persistent paths, migration policy, health checks, and rollback behavior are configured and reviewed.

Phase 40 is complete only after the real host cutover succeeds. Source preparation alone must never be reported as a live deployment.
