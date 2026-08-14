# NACS-Phil Turnstile Production Setup - Phase 29

Phase 29 prepares adaptive anti-bot protection for the public forms that accept information or credentials.

The integration is intentionally **disabled by default in local development**. It becomes active only when `TURNSTILE_ENABLED=true`.

## Protected entry points

- Contact / Inquiry submission
- Admissions preliminary application
- Admissions private tracking sign-in
- School Manager / Admin sign-in

Authenticated School Manager save/edit/delete actions are not challenged repeatedly. Admissions document uploads also remain protected by the existing private access workflow and rate limits instead of showing repeated CAPTCHA prompts.

## Visitor experience

Use a **Cloudflare Turnstile Managed widget** and keep the Blade widget at:

```text
data-appearance="interaction-only"
```

This lets normal visitors complete verification automatically whenever possible. The widget becomes visible only if visitor interaction is required.

The integration uses separate action names:

```text
inquiry
admissions_apply
admissions_track
admin_login
```

The server verifies that Siteverify returns the same expected action. Production can also require the exact hostname.

## Local development

The tracked `.env.example` keeps Turnstile disabled:

```dotenv
TURNSTILE_ENABLED=false
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
TURNSTILE_EXPECTED_HOSTNAME=
```

This preserves current local development and the existing Laravel test suite.

For a deliberate local browser test, use Cloudflare's current official testing credentials rather than production credentials on localhost.

## Production setup

When the final domain is known:

1. Create a Turnstile widget in the Cloudflare dashboard.
2. Choose Managed widget mode.
3. Allow only the real production hostname(s).
4. Do not allow `localhost` or `127.0.0.1` on the production widget.
5. Put the real site key and secret only in the production server `.env`.
6. Set the exact production hostname without `https://` in `TURNSTILE_EXPECTED_HOSTNAME`.
7. Set `TURNSTILE_ENABLED=true`.
8. Clear/config-cache the application.
9. Run `php artisan nacs:production-check --strict`.
10. Test all four protected forms in a real browser before DNS/live acceptance.

Example production values:

```dotenv
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=YOUR_CLOUDFLARE_TURNSTILE_SITE_KEY
TURNSTILE_SECRET_KEY=YOUR_CLOUDFLARE_TURNSTILE_SECRET_KEY
TURNSTILE_EXPECTED_HOSTNAME=YOUR-FINAL-DOMAIN
```

Never commit the real secret.

## Server-side enforcement

The browser-generated `cf-turnstile-response` token is sent to Laravel.

Laravel then calls Cloudflare Siteverify from the backend. The submission is accepted only when:

- Siteverify reports success.
- the returned action matches the protected form.
- the returned hostname matches `TURNSTILE_EXPECTED_HOSTNAME` when configured.

A missing token, rejected token, wrong action, wrong hostname, or verification-service failure stops the protected submission with a user-friendly retry message.

The token itself and the Turnstile secret are not written to the audit log.

## Existing defenses remain

Turnstile does not replace:

- Laravel CSRF protection
- existing endpoint throttles
- Contact/Admissions honeypots
- validation and consent requirements
- Admissions private access codes
- Admin temporary lockout
- Admin two-factor authentication
- HTTPS and secure production cookies

It adds an adaptive bot-abuse layer on top of them.

## CSP after Phase 33

Phase 33 adds explicit CSP directives. NACS-Phil permits Turnstile only from:

```text
https://challenges.cloudflare.com
```

for the required script, frame, and connection directives.

The public Facebook player is permitted only from:

```text
https://www.facebook.com
```

for `frame-src`.

Do not broaden `script-src` or `frame-src` to wildcard origins merely to solve a deployment problem.

## Privacy

The Privacy page discloses that protected forms may contact Cloudflare Turnstile for anti-bot verification. The school's final public privacy notice still requires school/privacy review before launch.

## Final browser checks

Before launch verify:

- normal desktop visitor submits each protected form with no persistent CAPTCHA annoyance.
- normal mobile visitor submits each protected form.
- a Managed challenge can become visible when interaction is required.
- a failed/expired verification produces a clear retry message.
- Contact rate limiting still works.
- Admissions Apply rate limiting still works.
- Admissions Track rate limiting still works.
- Admin account lockout and 2FA still work.
- no production secret is present in page source, Git, logs, or JavaScript.
