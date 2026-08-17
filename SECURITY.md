# Security Policy

NACS-Phil handles school administration and student information. Security changes must preserve least privilege, privacy, and existing regression coverage.

## Reporting

Security findings should be reported privately to the authorized NACS-Phil project administrators. Do not post real credentials, student data, access codes, authentication tokens, or private documents in public issues.

## Development rules

- Never commit `.env`, production credentials, database exports, session files, or private student documents.
- Use Laravel validation and parameterized database access; avoid raw SQL built from user input.
- Keep Blade output escaped by default. Raw HTML output requires an explicit trusted-content reason.
- Protect every object-level read/write/delete with role, permission, or relationship authorization.
- Keep confidential uploads outside the public web root.
- Do not weaken security tests simply to make a build pass.
- New APIs, payment processing, or AI features require a dedicated threat model and security phase before activation.

## Production rules

Production readiness depends on the real host. HTTPS, private database networking, backups, centralized logging/alerts, WAF controls, and incident-response procedures are external release gates and cannot be proven by source code alone.
