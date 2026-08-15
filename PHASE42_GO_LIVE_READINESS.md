# Phase 42 - Go-Live Readiness and Operations Hardening

Phase 42 consolidates the final source-side preparation for Phase 39 acceptance and Phase 40 production launch.

It does not fake manual acceptance and it does not deploy a live server.

## Added source protections

Phase 42 adds:

- branded, dependency-light 404, 419, 429, 500, and 503 error pages
- a shared error-page visual layer using the NACS navy, ivory, and gold system
- a go-live readiness aggregator command
- explicit verification that Laravel's `/up` health endpoint remains configured
- explicit verification that the production deployment workflow remains inactive
- explicit verification that student-document local fallback remains disabled
- updated final visual, staging, security, backup, monitoring, and cutover guidance

The error pages intentionally avoid database-dependent school settings and Vite so that a database or build problem is less likely to prevent the fallback page itself from rendering.

## Go-live command

Source-only readiness:

`php artisan nacs:go-live-check --source-only --strict`

This is safe for local development and CI. It checks tracked source contracts only.

Full external cutover readiness:

`php artisan nacs:go-live-check --strict`

This is expected to remain blocked on a local development machine. It becomes successful only when the existing production, host, data, browser/device, and recovery gates are genuinely complete on the real environment.

A normal non-strict run:

`php artisan nacs:go-live-check`

shows the source state and the remaining external cutover gates without printing secrets.

## Health monitoring

Laravel's existing `/up` endpoint is the preferred lightweight uptime probe. Monitoring systems should not use authenticated pages or private student endpoints as health checks.

The health endpoint must not expose:

- APP_KEY
- database credentials
- SMTP credentials
- Turnstile secrets
- student/admissions data
- server file paths

## Manual acceptance remains mandatory

The following cannot be truthfully automated:

- visual consistency on real browsers and devices
- touch and keyboard behavior
- real Turnstile challenge behavior
- real Facebook playback
- staging acceptance
- DNS/HTTPS verification on the final domain
- restore testing
- human review of content, consent, privacy, and school policy
- independent VAPT

Use the existing ignored local acceptance and recovery records only after those checks are actually performed.

## Repository publication rule

The Phase 42 installer may automatically publish and merge source changes only after:

- exact baseline and scope verification
- focused regression tests
- source-only go-live readiness
- Composer and npm security audits
- strict functional checks
- complete Laravel tests
- Vite production build
- Git whitespace checks
- green pull-request CI

After merge, it must also require the main-branch Quality Gate to pass before declaring the repository update successful.

This repository success is not the same as production launch success.

## Independent security review

Before broad SIS use with real family data, schedule an independent VAPT or equivalent professional application-security review. Internal automated tests and dependency audits are important but are not a substitute for an independent adversarial assessment.

Repository publication success is not proof that the site is already live.
