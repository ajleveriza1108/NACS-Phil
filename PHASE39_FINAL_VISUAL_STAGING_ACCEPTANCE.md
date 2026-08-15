# Phase 39 - Final Visual and Staging Acceptance

Phase 39 separates automated source readiness from manual visual, browser, device, and staging acceptance that only a real reviewer can confirm.

## Automated prerequisites

Before manual acceptance starts, the approved source must pass:

- Composer validation and security audit
- npm high/critical audit
- `php artisan nacs:functional-check --strict`
- `php artisan nacs:go-live-check --source-only --strict`
- the complete Laravel test suite
- the Vite production build
- `git diff --check`
- exact changed-file and privacy gates

Passing these checks does **not** mark visual acceptance complete.

## Manual visual acceptance

Review at minimum:

- Home
- About
- Programs
- Admissions, application, receipt, tracking, and status screens
- News and Events
- Gallery
- Media Hub
- Faculty, Calendar, and Documents
- Privacy and Contact
- Admin / School Manager
- Admin sign-in and two-factor challenge
- Page image management
- Student Records / SIS
- Student & Parent Portal sign-in, dashboard, student record, and password change
- 404, 419, 429, 500, and 503 error screens

Review desktop, tablet portrait/landscape, large phone, small phone, and approximately 320px width. Confirm no cropped controls, horizontal overflow, unusable menus, hidden focus states, broken image fallbacks, unreadable form errors, inconsistent branding, or misleading stock imagery.

The public site, admissions experience, administration area, SIS, authentication family, and error screens must use one recognizable NACS visual language while still preserving the security needs of private screens.

Phase 36 illustrative learning photography must remain clearly distinguishable from official NACS student/staff/facility/event photography.

## Workflow acceptance

Use synthetic or disposable QA records only. Confirm:

- inquiry submission
- preliminary admission application and tracking
- private requested-document upload/download
- Teacher Draft -> Principal Review -> Published workflow
- staff permissions and inactive-account blocking
- admin login, lockout, two-factor authentication, session revocation, and logout
- student creation and teacher assignment
- grade and attendance entry
- guardian linking
- finance access restricted to leadership
- student and parent isolation
- forced portal password change
- private student document access boundaries

Do not use real student records merely to prove a test.

## Browser/device record

The ignored local file `.nacs-browser-acceptance.json` is the manual acceptance record used by:

`php artisan nacs:acceptance-check --strict`

Do not set a check to true unless it was actually performed.

## Staging gate

A real staging environment requires host/provider details and is an external deployment activity. Use production-like PHP, database, storage, cache, session, Turnstile, and mail settings without copying live secrets into Git.

On staging run:

- `php artisan nacs:host-check --strict`
- `php artisan nacs:staging-check --strict`
- `php artisan nacs:acceptance-check --strict`
- `php artisan nacs:recovery-check --strict`
- `php artisan nacs:go-live-check`

Record staging acceptance separately. Do not edit this tracked document to claim an acceptance that has not happened.

## Result

Source readiness can be automated. Manual visual/device review and staging acceptance remain explicit release gates.
