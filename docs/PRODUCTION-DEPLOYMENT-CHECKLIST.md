# NACS-Phil Production Deployment Checklist - Phase 14

This checklist is for the tested NACS-Phil school website release candidate. It contains no passwords, API keys, applicant records, or hosting credentials.

## Release gate
Before any public deployment:

- Phase 12 focused backend tests must pass.
- Phase 13 Admin experience tests must pass.
- Phase 14 production-hardening tests must pass.
- The complete Laravel regression suite must pass.
- The Vite production build must succeed from the existing locked dependencies.
- Laravel config, route, and view caches must build successfully during the deployment rehearsal.
- The backup and restore drill must verify matching database and storage manifests.
- Git staging must contain no private/runtime files.

## Manual school approval
The school must review the finished site on phone, tablet, laptop, desktop, and a narrow 320px viewport. Confirm:

- official school name and address
- official contact details and office hours
- current school year
- admissions requirements and wording
- mission, vision, Christian statements, and school history
- all faculty/staff profiles
- all downloadable documents
- all public calendar dates
- privacy and child-protection wording
- authorization and appropriate consent for every published photograph of identifiable children

Development placeholders must not be treated as official school statements.

## Production environment
Create a private production `.env` on the hosting server. Never commit it.

Required production posture:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<final-domain>`
- a protected production `APP_KEY`
- production database credentials
- `SESSION_DRIVER=database` or another host-supported persistent driver
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true` after HTTPS is active
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- production mail settings if outbound mail is later enabled

Do not copy development passwords, access codes, or test credentials into production.

## Server requirements
The host must support the PHP and Laravel versions used by the tested release, the required PHP extensions, Composer for production dependency installation, a supported production database, HTTPS, writable Laravel storage/cache directories, and a document root that points to Laravel's `public` directory.

## Deployment order

1. Take a verified production backup/checkpoint if replacing an existing deployment.
2. Deploy the exact approved GitHub release source.
3. Create the private production `.env` outside version control.
4. Install production Composer dependencies with development dependencies excluded.
5. Build or upload the exact approved Vite production assets.
6. Configure the production database.
7. Run pending Laravel migrations.
8. Transfer approved public media.
9. Transfer private school documents and admissions files only through an authorized secure process.
10. Make `storage` and `bootstrap/cache` writable by the application process.
11. Point the domain document root to Laravel's `public` directory.
12. Enable HTTPS before enabling secure-only cookies.
13. Set `APP_DEBUG=false`.
14. Build production configuration, route, and view caches.
15. Verify the security response headers.
16. Test the public website, Admin login, 2FA where enabled, inquiry workflow, content review, Gallery consent protection, school documents, and Admissions access control.
17. Confirm backups are scheduled and restorable.
18. Keep the local project as the development copy; test changes locally before future deployment.

## Never publish or commit

- `.env` or environment backups
- SQLite databases or database sidecars
- `.nacs-backups`
- private school documents
- private admissions documents
- staff/applicant records exported from the database
- application logs
- session/cache runtime files
- passwords, TOTP secrets, recovery codes, applicant access codes, or hosting credentials

## Security headers in Phase 14
The application applies conservative headers including `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and a restricted `Permissions-Policy`. Admin and private admissions responses are marked `no-store`. HSTS is emitted only for HTTPS production requests.

A restrictive Content Security Policy is intentionally not forced in this phase because the existing application must first inventory all inline and generated frontend resources. CSP should be introduced later with report-only testing before enforcement.

## After launch
Maintain verified backups of:

- production database
- approved public media
- private school documents
- private admissions files
- production environment configuration stored securely outside the public web root

Periodically perform a restore drill instead of assuming backups are usable.
