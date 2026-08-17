# NACS-Phil Phase 50 - Input, Upload, and Abuse Hardening

Phase 50 adds risk-based throttling to sensitive authenticated write operations that previously relied only on authentication/authorization.

Added throttle coverage includes:
- student grades;
- attendance;
- finance;
- teacher assignments and decisions;
- guardian relationships;
- confidential student document registration;
- family admissions document deletion;
- staff account creation/update and 2FA reset;
- administrative media upload/removal;
- branding logo upload/removal.

Existing allowlists are preserved for student profile images and external-only confidential student documents.

Command:

`php artisan nacs:input-abuse-baseline --strict`

This phase does not weaken existing Turnstile, validation, permission, or private-storage controls.
