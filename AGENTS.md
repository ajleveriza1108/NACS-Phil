# NACS-Phil Codex / Agent Instructions

These instructions apply to the entire repository unless a more specific `AGENTS.md` is added in a subdirectory.

## Project identity

- Project: Noel Academy Christian of Sariaya Philippines, Inc. (NACS-Phil)
- Framework: Laravel 13
- Primary Windows project path: `D:\Web Projects\NACS-Phil`
- Primary shell compatibility: Windows PowerShell 5.1
- Local development database: SQLite
- Intended production database: MySQL or MariaDB
- Public media surfaces: Gallery and Media Hub
- Media Hub route: `/media`
- Gallery route: `/gallery`

## General working rules

1. Inspect the repository and Git state before making changes.
2. Preserve working behavior unless the task explicitly requires a change.
3. Prefer the smallest safe change over broad rewrites.
4. Use existing Laravel models, controllers, validation, storage disks, permissions, and admin/CMS workflows instead of bypassing them.
5. Do not invent school facts, history, statistics, faculty identities, programs, addresses, phone numbers, email addresses, or credentials.
6. Never add Senior High unless the school explicitly supplies and approves it. Current supported levels are Nursery, Pre-Kindergarten, Kindergarten, Elementary Grades 1-6, and Junior High Grades 7-10.
7. Preserve the approved visual direction: deep navy, warm white/ivory, restrained warm gold, official school logo, rounded modern cards, subtle shadows, responsive layouts, and a dignified Christian-school presentation.
8. Preserve 320px narrow-phone, phone, tablet portrait/landscape, laptop, desktop, and ultrawide behavior. Avoid horizontal overflow, clipped buttons, cropped logos, inaccessible focus, and motion that ignores `prefers-reduced-motion`.

## Source-control safety

Never run or recommend these destructive operations without explicit user approval:

- `git reset --hard`
- `git clean`
- force push
- `git push --force`
- `git push --force-with-lease`

Before a source change:

```text
git status --short
git branch --show-current
git rev-parse HEAD
git fetch origin
```

If the working tree contains unrelated changes, stop and report them rather than overwriting them.

Do not commit secrets, runtime databases, private uploads, local backups, or operational gate records.

## Secrets and private data

Never expose, log, commit, copy into prompts, or publish:

- `.env`
- APP_KEY
- database credentials
- SMTP credentials
- Cloudflare Turnstile secret keys
- passwords or 2FA secrets
- admissions access codes
- family/student inquiry content
- admissions documents
- private application records
- private backups

Keep private admissions storage outside the public web root.

Do not use GitHub as a transfer channel for private family/student data.

## Roles and permissions

Preserve existing role boundaries:

- Super Admin / Web Developer: full system and staff-account access
- Principal: school content, admissions, inquiries, official settings, reviews/approvals, and permitted Safe Trash actions
- Teacher: permitted daily content workflows; publication remains subject to configured review/approval rules

Never weaken authorization simply to make a test pass.

Preserve authentication, 2FA behavior, CSRF, throttling, Turnstile, content review rules, Safe Trash, permanent-delete restrictions, audit history, and private admissions access controls.

## Gallery and image privacy

The Gallery is an approved-photo surface, not a generic public file dump.

Preserve the existing Gallery consent and publication rules.

For school photographs:

1. Prefer campus, facilities, classrooms without identifiable children, chapel/Christian symbols, signage, decorations, stage/event setups, and other low-risk institutional images.
2. Public Facebook posting alone is not proof of separate website-photo consent.
3. Do not automatically mark `consent_confirmed_at` or equivalent fields true merely because an image appeared on Facebook.
4. Images with identifiable minors require school approval/consent consistent with the existing Gallery workflow.
5. Do not publish images exposing student names, IDs, grades, birth dates, phone numbers, addresses, medical information, admissions information, certificates with private details, or similar sensitive data.
6. Never perform face recognition or infer the identity of a person from an image.
7. Use accurate alt text and captions; do not invent photographer credit.

## Official Facebook media workflow

When explicitly asked to use the school's official Facebook page, the intended source is:

`https://www.facebook.com/nacsphilguisguis`

Use the browser normally. Do not bypass Facebook authentication, anti-bot protections, or access controls.

If login is required, ask the user to sign in manually. Never ask for or store Facebook credentials in source files or prompts.

Use only public material from the official school page. Do not scrape private profiles, messages, comments, reactions, contact details, friend lists, or unrelated accounts.

For candidate images:

- choose quality over quantity
- reject blurry thumbnails, duplicates, Facebook UI screenshots, unrelated memes, private-data images, and poor crops
- preserve original candidate files separately from optimized website derivatives
- do not upscale low-resolution sources into fake high resolution
- strip unnecessary EXIF/GPS metadata from published derivatives when practical with already available tools

Prefer existing CMS/admin upload flows and storage disks rather than hardcoding external Facebook image URLs into Blade.

## Media Hub

Preserve the existing `/media` behavior:

- All Media
- Photos
- Videos
- Live

The Media Hub combines approved Gallery photos with validated public Facebook video and Facebook Live links.

Do not add direct MP4 hosting unless explicitly requested. The current strategy keeps large video files hosted by Facebook to reduce school-hosting storage and bandwidth.

Preserve HTTPS Facebook URL validation, allowed Facebook host validation, Draft / Published / Archived workflow, Featured behavior, Safe Trash, privacy disclosure, embedded Facebook playback, and dedicated `/gallery` functionality.

Do not weaken CSP to `*` or broad `https:` sources to make embeds work. Add only exact approved origins when genuinely necessary.

## Homepage hero and images

The homepage hero supports real school photography but must retain the safe fallback school seal.

Do not reintroduce the historical fallback-logo crop bug. The fallback logo must remain contained and fully visible; full-bleed campus photography may continue using appropriate `object-fit: cover`.

For hero photography verify focal-point/cropping quality on:

- 320px narrow phone
- normal phone
- tablet portrait
- tablet landscape
- laptop
- desktop
- ultrawide

## Content integrity

Do not infer or fabricate school history, enrollment counts, teacher/student ratios, graduation statistics, principal/faculty names, awards, contact information, dates, or program offerings.

Only attach a Facebook image to News or Events when the relationship is factual and supported by the existing content/post.

For Faculty/Staff photos, do not identify a person from an image. Use a photo only when the existing CMS already identifies that staff member and the image/permission is clearly appropriate.

## Test discipline

Do not modify tests merely to hide a real failure.

For meaningful source changes, run focused tests first, then the full suite.

Core local QA commands:

```text
php artisan optimize:clear
php artisan nacs:functional-check --strict
php artisan test --stop-on-failure
npm.cmd run build
git diff --check
git status --short
```

Relevant focused tests for Gallery / Media Hub / public-site changes:

```text
php artisan test tests/Feature/GalleryContentTest.php --stop-on-failure
php artisan test tests/Feature/Phase21NewsEventsGalleryFidelityTest.php --stop-on-failure
php artisan test tests/Feature/Phase22FacebookMediaHubTest.php --stop-on-failure
php artisan test tests/Feature/Phase24FinalReleaseAuditTest.php --stop-on-failure
php artisan test tests/Feature/Phase27FullFunctionalInteractionAuditTest.php --stop-on-failure
php artisan test tests/Feature/Phase28FullCrudPermissionAuditTest.php --stop-on-failure
php artisan test tests/Feature/Phase33SecurityRecoveryHardeningTest.php --stop-on-failure
php artisan test tests/Feature/PostFinalizationMediaHubPolishTest.php --stop-on-failure
```

Run `php -l` on changed PHP files.

Do not falsely mark a staging/production gate as passed locally when it depends on real HTTPS, host configuration, Turnstile keys, DNS, backup restoration, or physical acceptance.

## Browser QA

For public UI changes, visually test the running site rather than relying only on automated tests.

If port 8000 is occupied by another application, NACS-Phil may be run locally on port 8001:

```text
php artisan serve --host=127.0.0.1 --port=8001
```

Check major public pages, navigation, Resources menu, Gallery filters/lightbox, Media Hub filters, forms, images, console/network errors, and responsive behavior.

Authenticated Admin browser testing may require the user to enter credentials manually. Never request passwords in the prompt or commit them.

## Release and deployment distinction

A visually complete local site is not automatically production-ready.

Keep these states distinct:

- repository/source ready
- local browser QA passed
- staging ready
- staging verified
- production cutover ready
- post-launch verified

Real Turnstile, HTTPS secure-cookie behavior, HSTS/proxy behavior, external Facebook playback, private upload/download flows, backup restoration, DNS, and SSL are staging/production verification items.

## Known security review items

Do not silently weaken or dismiss existing review findings. Security work should explicitly evaluate and test:

- privileged 2FA enforcement
- forced password-reset enforcement
- session invalidation after password/security resets
- admissions access-code rotation invalidating active portal sessions
- login lockout/account-denial-of-service behavior
- reachability of unapproved/draft public-storage images
- Gallery lightbox focus trapping
- active-filter accessibility semantics such as `aria-current`

If asked to fix these, address them individually with focused tests and regression review rather than one broad security rewrite.

## Presentation-media preparation

When preparing a presentation-ready copy for school leadership:

- use authentic school imagery where appropriate
- keep consent/privacy boundaries intact
- prefer existing CMS workflows
- do not fake approval flags
- keep a source/provenance manifest for imported candidate media in a Git-ignored working location
- visually review Home, Programs, Admissions, Gallery, and Media Hub before declaring presentation ready

A presentation-ready local/staging copy may use only content safe and authorized for that purpose. Production readiness remains a separate decision.

## GitHub future-update workflow

For future repository updates:

- start code/design work from the latest approved `main`
- use an `agent/*` or other approved feature branch
- open a pull request into `main`
- require the repository quality gate in `.github/workflows/quality-gate.yml`
- keep production deployment sourced from `main`, never from a feature branch
- never commit or deploy a local `.env`, SQLite database, private uploads, admissions files, presentation-only media, logs, or backups
- preserve production `.env`, database data, uploaded files, and private storage across deployments

Do not enable production auto-deployment merely because the source is on GitHub. The repository's production deployment template must remain inactive until the actual host, staging environment, authenticated deployment path, backup/rollback process, persistent storage behavior, and database migration procedure have been verified.

When production auto-deployment is eventually enabled, an approved merge into `main` may trigger deployment only after the required GitHub/staging gates pass.
