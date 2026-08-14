# NACS-Phil Administrator and Maintenance Handbook

## Roles

### Super Admin / Web Developer

Responsible for system-level settings, staff accounts, permanent-delete boundaries, deployment, backups, security, and release verification.

### Principal

Manages school content, admissions, inquiries, official settings, review/approval, and Safe Trash restore within permitted boundaries.

### Teacher

Uses the daily content workflow permitted by the School Manager. Teacher publication remains subject to the configured review/approval rules.

## Daily content

Use School Manager for:

- Announcements
- Events
- Gallery photos
- Live & Videos
- Faculty & Staff
- Academic Calendar
- Documents
- website page content

Use Safe Trash rather than bypassing the application to delete database rows or files directly.

## Family/private information

Inquiry and admissions information is private school-office data.

Do not:

- copy admissions records into public documents
- publish admissions uploads
- email access codes in insecure bulk exports
- place private files under `public`
- upload database/SQL/SQLite copies to GitHub

## Account security

- use unique passwords
- enable 2FA for leadership/admin accounts
- disable inactive staff accounts
- do not share a Super Admin login
- investigate unexpected lockouts or repeated failed logins

## Release commands

Local/repository:

```text
php artisan nacs:functional-check --strict
php artisan test
npm run build
```

Staging/production preparation:

```text
php artisan nacs:host-check --strict
php artisan nacs:production-check --strict
php artisan nacs:data-audit --strict
php artisan nacs:staging-check --strict
php artisan nacs:acceptance-check --strict
php artisan nacs:recovery-check --strict
php artisan nacs:cutover-check --strict
```

After live DNS/SSL cutover:

```text
php artisan nacs:post-launch-check --live-http --strict
```

## Local-only gate records

These files are intentionally ignored by Git:

- `.nacs-data-migration-decision.json`
- `.nacs-browser-acceptance.json`
- `.nacs-recovery-verification.json`

They record operational decisions/tests; they do not belong in the public source repository.

## Backups

Back up database, private admissions files, and public media. Maintain an off-account copy. A backup is not considered verified until restoration has actually been tested.

## Incident response

If a security/privacy incident is suspected:

1. preserve relevant logs
2. limit access to the affected account/system
3. rotate exposed credentials/secrets
4. do not delete evidence needed for investigation
5. assess whether private family/student information was exposed
6. follow the school's applicable privacy/legal notification procedure
7. restore only from a known-good verified backup when necessary

## Maintenance principle

Keep the website orderly, tested, and reviewable. "Let all things be done decently and in order." - 1 Corinthians 14:40, KJV.
