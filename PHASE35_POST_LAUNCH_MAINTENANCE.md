# NACS-Phil Phase 35 - Post-Launch Verification and Maintenance Baseline

After the production domain is live, run from the production release:

```text
php artisan nacs:post-launch-check --live-http --strict
```

The live mode makes read-only GET requests to the configured final `APP_URL` for Home, About, Programs, Admissions, Contact, Privacy, and robots.txt. It also checks the main response for CSP, HSTS, and `nosniff`.

## Immediately after launch

- submit Contact through the real browser flow
- test Admissions Apply and Track
- verify Turnstile on desktop/mobile
- verify Admin login and 2FA
- verify one public Facebook video
- verify one approved public media image
- verify one authorized private document download
- inspect Laravel logs for unexpected errors without exposing them publicly
- confirm scheduled/provider backups are running
- keep the pre-cutover backup until the new release has been stable

## Weekly

- review failed logins and unusual application errors
- review pending content/admissions/inquiries
- verify public pages and key navigation
- verify backup job status
- check storage/database capacity

## Monthly

- perform a backup restore test or follow the approved restore-test cadence
- review staff accounts and remove/disable accounts no longer required
- review permissions and 2FA adoption
- review Turnstile analytics for unusual bot activity
- review PHP/Laravel dependency advisories before scheduling updates
- test updates on staging before production

## Update procedure

1. Back up production.
2. Pull/deploy only the tested Git commit.
3. Install production dependencies using the provider-approved process when dependencies changed.
4. Build/deploy frontend artifacts when frontend dependencies/assets changed.
5. Run database migrations only after reviewing them and confirming backup/rollback.
6. Run `php artisan optimize`.
7. Run production, cutover, and post-launch checks.
8. Smoke test public forms and Admin.
9. Keep rollback material until the update is verified.

Never edit production source ad hoc and then overwrite it with Git without first understanding the difference.
