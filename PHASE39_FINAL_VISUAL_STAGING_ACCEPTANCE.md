# Phase 39 — Final Visual & Staging Acceptance

Phase 39 separates automated release checks from the manual browser/device acceptance that only a person can confirm.

## Automated prerequisites

The cumulative Phase 38–41 installer must pass:

- Composer validation and security audit
- npm high/critical audit
- `php artisan nacs:functional-check --strict`
- the complete Laravel test suite
- the Vite production build
- `git diff --check`
- exact changed-file and privacy gates

Passing these checks does **not** mark visual acceptance complete.

## Manual visual acceptance

Before a production cutover, review at minimum:

- Home
- About
- Programs
- Admissions and application/tracking screens
- News and Events
- Gallery
- Media Hub
- School Resources
- Admin / School Manager
- Page image management
- Student Records
- Student & Parent Portal

Review desktop, tablet portrait/landscape, large phone, small phone, and approximately 320px width. Confirm no cropped controls, horizontal overflow, unusable menus, hidden focus states, misleading stock imagery, or broken image fallbacks.

Phase 36 illustrative learning photography must remain clearly distinguishable from official NACS student/staff/facility/event photography.

## Staging gate

A real staging environment requires host/provider details and is an external deployment activity. Use production-like PHP/database/storage/session settings without copying live secrets into Git.

Record staging acceptance separately. Do not edit this tracked document to claim an acceptance that has not happened.

## Result

Source readiness can be automated. Manual visual/device review and staging acceptance remain explicit release gates.
