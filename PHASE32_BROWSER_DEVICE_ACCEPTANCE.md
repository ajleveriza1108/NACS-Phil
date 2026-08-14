# NACS-Phil Phase 32 - Real Browser and Device Acceptance

Phase 27 and Phase 28 already automate route, form, CRUD, permission, storage, and Safe Trash behavior. Phase 32 adds the gate that automation cannot honestly replace: physical browser/device interaction.

## Start the local or staging site

For local review:

```text
php artisan serve --host=127.0.0.1 --port=8000
```

For final acceptance, use the HTTPS staging site so Turnstile, secure cookies, uploads, and external Facebook playback are exercised in the deployment environment.

## Acceptance record

Copy:

```text
BROWSER_DEVICE_ACCEPTANCE.example.json
```

to:

```text
.nacs-browser-acceptance.json
```

The real record is ignored by Git.

Run:

```text
php artisan nacs:acceptance-check
php artisan nacs:acceptance-check --strict
```

Do not set an item to `true` because an automated test passed. Mark it only after the behavior was physically verified.

## Minimum viewport/device matrix

- 320 px narrow phone
- standard phone
- tablet portrait
- tablet landscape
- laptop
- desktop
- large/ultrawide

## Required interaction matrix

Test every public navigation item and CTA, Resources menu, News/Events detail links, Gallery filter/lightbox, real Facebook playback, Contact, Admissions Apply, Admissions Track, Admin login/2FA, CRUD, Safe Trash, uploads/downloads, keyboard focus, reduced motion, and clipped/overflow behavior.

Turnstile must be checked on both desktop and mobile and must include a test where the challenge visibly requires interaction.

Phase 32 is complete only when `nacs:acceptance-check --strict` succeeds on the staging acceptance record.
