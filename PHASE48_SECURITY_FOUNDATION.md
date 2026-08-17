# NACS-Phil Phase 48 - Security Foundation and Future-Ready Hardening

## Purpose

Phase 48 adds security improvements that are safe to implement before the final production host is selected. It does not invent a mobile API, live payments, AI generation, or production infrastructure that does not exist yet.

## Implemented now

- Dedicated daily security event log.
- Security metadata logger with allowlisted fields only.
- Authentication success/failure/lockout/logout security events for staff and portal users.
- 2FA setup/enable/disable/challenge security events without logging secrets or codes.
- 401/403/419/429/5xx security-response logging without request payloads.
- HMAC-based IP/user-agent fingerprints for correlation without storing raw values.
- Strong symbol requirement for staff password changes.
- Other database-backed sessions revoked after password changes.
- Additional rate limits on staff security actions, student linking, profile photos, report cards, transcripts, and portal password changes.
- Automated cross-teacher IDOR regression coverage.
- Automated input/upload/rate-limit security-contract checks.
- Read-only `php artisan nacs:security-baseline` command.

## Existing controls preserved

- Laravel password hashing.
- Session regeneration after successful login.
- Account lock after repeated failed sign-in attempts.
- Invite-only account activation and OTP verification.
- Existing Turnstile public-form protection.
- CSP/security headers and production HSTS behavior.
- Relationship-aware student authorization through `StudentAccess`.
- Private student profile-photo storage.
- Existing staff least-privilege permissions.
- Existing admissions privacy controls.

## Production-only work intentionally deferred

The following require the real hosting/network environment and are not falsely implemented by this phase:

1. Real TLS certificate and HTTP-to-HTTPS redirect validation.
2. Firewall/private-network restriction for the production database.
3. Production secret manager or host-provided secret injection.
4. Centralized logging retention and alerts.
5. WAF/CDN bot and scraping controls.
6. Backup scheduling plus verified restore drills.
7. External uptime monitoring.
8. Independent VAPT and production access review.

Run `php artisan nacs:security-baseline --production --strict` only on the real production candidate. It is expected to fail in local development.

## Future security roadmap

### Phase 49 - Authorization and IDOR Hardening
Expand multi-account attacker tests across student records, admissions, documents, finance, staff management, and all future API resources. Default-deny every protected object.

### Phase 50 - Input, Upload, and Abuse Hardening
Complete an input-surface inventory for every form, query parameter, route parameter, upload, and future API field. Add risk-based throttles and file-type/content validation where gaps are found.

### Phase 51 - Authentication and Session Hardening
Add an audited password-recovery flow when real transactional email is available. Stage mandatory 2FA for privileged roles after recovery procedures and administrator training are ready.

### Phase 52 - Production Security and Monitoring
After the host is chosen, enforce HTTPS, private database networking, production secrets, centralized monitoring, WAF rules, backup/restore drills, and alerting.

### Future API/Mobile Security
Use the same identities, permissions, `StudentAccess` rules, and data classifications. Do not build a weaker second authorization model.

### Future Payment Security
The payment gateway owns payment credentials. NACS stores only provider-neutral references/status/amount metadata. Require signed webhooks and idempotency before activation.

### Future AI Security
Only if AI is introduced: per-user quotas, rate limits, cost limits, prompt/data classification, and strict rules preventing confidential student data from being sent to unapproved providers.

## Logging privacy rule

Never log passwords, OTPs, TOTP secrets, recovery codes, reset/invitation tokens, raw session identifiers, full request payloads, birth certificates, private student documents, or unnecessary student/guardian PII.
