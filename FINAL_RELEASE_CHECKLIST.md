# NACS-Phil Final Release Checklist — Phase 24

This file is a deployment checklist, not a statement that the site is already production-ready.

## Before launch

- Open **School Manager → Launch Readiness** and resolve all school/content/security blockers.
- Confirm official school name, address, school year, office hours, public contact method, and privacy contact.
- Confirm the official logo/crest upload has been approved in Branding.
- Review Mission, Vision, Christian statements, school history, programs, admissions wording, dates, faculty profiles, downloads, and contact details.
- Confirm every identifiable child image has appropriate school authorization/consent.
- Review the Privacy & Child Protection page with the school and qualified Philippine privacy counsel.
- Test one real Facebook recorded video and one Facebook Live/replay on desktop and phone.
- Complete a disposable admissions application end-to-end, including private tracking and requested-document upload.
- Complete Teacher Draft → Principal Review → Published content workflow.
- Confirm Principal and Super Admin accounts use two-factor authentication.

## Production environment

Set these on the hosting server, not in Git:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<final-domain>`
- production `APP_KEY`
- production database credentials
- secure mail settings if email delivery is enabled

Use the production database selected for hosting. Do not publish the local development SQLite database.

## Deployment sequence

1. Back up the production database and persistent uploads.
2. Deploy the tested Git commit.
3. Install production PHP dependencies with Composer using the production/no-dev options appropriate to the host.
4. Build frontend assets from the locked project dependencies, or deploy the already-tested production build according to the hosting workflow.
5. Run `php artisan migrate --force`.
6. Ensure only intended public media is exposed through Laravel storage/public links; admissions documents must remain private.
7. Run `php artisan optimize:clear`.
8. Run production caches (`config:cache`, `route:cache`, `view:cache`) if supported by the host.
9. Verify HTTPS and the final domain.
10. Re-open School Manager → Launch Readiness on production.
11. Smoke-test Home, About, Programs, Admissions, News, Events, Gallery, Contact, Faculty, Calendar, Documents, Live & Videos, Privacy, and Admissions Apply/Track.
12. Confirm Admin login, 2FA, content publishing, Safe Trash, Audit History, and private admissions-document download.

## Final device/browser check

Review at minimum:

- 320px phone
- 360px phone
- modern Android phone
- portrait tablet
- landscape tablet
- laptop
- desktop
- ultrawide desktop

Verify no horizontal overflow, clipped buttons, unreadable text, dark-on-dark text, duplicate navigation, broken characters, inaccessible focus states, or cropped forms.

## Release rule

Do not treat automated tests alone as final approval. Production launch requires both:

1. automated Phase 24 QA to pass; and
2. the school/manual Launch Readiness checks to be reviewed and accepted.
