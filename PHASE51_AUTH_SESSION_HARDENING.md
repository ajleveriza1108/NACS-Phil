# NACS-Phil Phase 51 - Authentication and Session Hardening

Phase 51 strengthens authentication-state transitions without introducing an unsafe recovery flow.

Implemented now:
- CSRF token rotation after successful staff login;
- CSRF token rotation after staff password changes;
- CSRF token rotation after 2FA enable/disable/challenge success;
- CSRF token rotation after portal password changes;
- existing other-session revocation remains intact;
- existing strong password contract remains intact;
- existing recovery codes remain hashed.

Staged privileged 2FA:
- middleware is registered and wired into the admin boundary;
- `NACS_PRIVILEGED_2FA_REQUIRED=false` by default;
- target roles are `super_admin` and `principal`;
- security setup/logout routes remain available when enforcement is enabled.

Do not enable mandatory privileged 2FA until recovery procedures and administrator training are ready.

Command:

`php artisan nacs:auth-session-baseline --strict`

Transactional-email password recovery is not invented by this phase.
