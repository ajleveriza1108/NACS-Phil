# NACS-Phil Final Release Checklist - Current Go-Live Baseline

This file is a deployment checklist, not a statement that the site is already production-ready.

## Before launch

- Open **School Manager -> Launch Readiness** and resolve all school/content/security blockers.
- Confirm official school name, address, school year, office hours, public contact method, and privacy contact.
- Confirm the official logo/crest upload has been approved in Branding.
- Review Mission, Vision, Christian statements, school history, programs, admissions wording, dates, faculty profiles, downloads, and contact details.
- Confirm every identifiable child image has appropriate school authorization/consent.
- Review the Privacy & Child Protection page with the school and qualified Philippine privacy counsel.
- Test one real Facebook recorded video and one Facebook Live/replay on desktop and phone.
- Complete a disposable admissions application end-to-end, including private tracking and requested-document upload.
- Complete Teacher Draft -> Principal Review -> Published content workflow.
- Confirm Principal and Super Admin accounts use two-factor authentication.
- Complete Student Records and Student & Parent Portal workflow acceptance using synthetic/disposable records.
- Review the branded 404, 419, 429, 500, and 503 error screens.
- Run `php artisan nacs:go-live-check --source-only --strict`.

## Production environment

Set these on the hosting server, not in Git:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<final-domain>`
- production `APP_KEY`
- production database credentials
- encrypted secure server-side session settings
- persistent cache
- production Turnstile site/secret/hostname
- secure mail settings if email delivery is enabled
- approved private student-document provider

Use the production database selected for hosting. Do not publish the local development SQLite database.

Private admissions documents must remain private, and student documents must not receive a local-public fallback.

## Backup and recovery

Before running a production migration:

- create a database backup
- back up persistent private and public files
- keep an off-account protected copy
- test database restore
- test private-file restore
- record only completion state in `.nacs-recovery-verification.json`
- run `php artisan nacs:recovery-check --strict`

## Deployment sequence

1. Freeze the exact approved `main` commit.
2. Back up the production database and persistent uploads.
3. Deploy the exact approved commit.
4. Install production PHP dependencies from the lockfile.
5. Build or deploy the exact approved production frontend assets.
6. Review every pending migration.
7. Run migrations only after the backup/rollback point is confirmed.
8. Ensure only intended public media is exposed; admissions documents must remain private.
9. Verify the approved private student-document provider.
10. Run `php artisan optimize:clear`.
11. Run production caches (`config:cache`, `route:cache`, `view:cache`) if supported by the host.
12. Verify HTTPS, final domain, storage, cache, sessions, Turnstile, and mail.
13. Verify `/up`.
14. Run `php artisan nacs:production-check --strict`.
15. Run `php artisan nacs:host-check --strict`.
16. Run `php artisan nacs:data-audit --strict`.
17. Run `php artisan nacs:acceptance-check --strict`.
18. Run `php artisan nacs:recovery-check --strict`.
19. Run `php artisan nacs:cutover-check --strict`.
20. Run `php artisan nacs:go-live-check --strict`.
21. Smoke-test Home, About, Programs, Admissions, News, Events, Gallery, Contact, Faculty, Calendar, Documents, Live & Videos, Privacy, and Admissions Apply/Track.
22. Confirm Admin login, 2FA, content publishing, Safe Trash, Audit History, Student Records, portal login/password change, and private document access.

## Final device/browser check

Review at minimum:

- approximately 320px phone
- 360px phone
- modern Android phone
- portrait tablet
- landscape tablet
- laptop
- desktop
- ultrawide desktop

Verify no horizontal overflow, clipped buttons, unreadable text, dark-on-dark text, duplicate navigation, broken characters, inaccessible focus states, inconsistent authentication/error themes, or cropped forms.

## First-launch monitoring

Configure external uptime monitoring for `/up` and watch server/application logs for:

- HTTP 5xx errors
- unusual authentication failures
- Turnstile failures
- mail failures
- database/storage failures
- failed uploads/downloads
- capacity problems
- TLS certificate expiration

## Release rule

Do not treat automated tests alone as final approval. Production launch requires both:

1. automated Phase 24 QA to pass, together with the later Phase 39-42 source/security gates; and
2. the school/manual Launch Readiness, browser/device, staging, backup/restore, and real-host cutover checks to be reviewed and accepted.

Repository publication success is not proof that the site is already live.
