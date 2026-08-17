# NACS-Phil Production Security Evidence Checklist

Do not set a flag true merely to make a readiness command pass. Each flag records evidence from the real production environment.

## TLS / HTTPS
- [ ] Real hostname has a valid certificate.
- [ ] HTTP redirects to HTTPS.
- [ ] Admin, admissions, student, and parent sessions remain HTTPS-only.
- [ ] Then set `NACS_PROD_TLS_HTTPS_VERIFIED=true`.

## Database exposure
- [ ] Production database port is not publicly reachable.
- [ ] Only approved private/application sources can connect.
- [ ] Credentials are supplied outside source control.
- [ ] Then set `NACS_PROD_DATABASE_PRIVATE_VERIFIED=true`.

## Backup / restore
- [ ] Automated backups exist.
- [ ] A restore has succeeded in a safe recovery environment.
- [ ] Responsible staff and protected storage are documented.
- [ ] Then set `NACS_PROD_BACKUP_RESTORE_VERIFIED=true`.

## Central security logging
- [ ] Security logs have an approved retention period.
- [ ] Important authentication/security events reach the selected central service.
- [ ] Alert delivery is tested without passwords, OTPs, tokens, or request payloads.
- [ ] Then set `NACS_PROD_CENTRAL_LOGGING_VERIFIED=true`.

## WAF / CDN
- [ ] Provider-supported protections are configured where appropriate.
- [ ] Rules do not break approved login, admissions, Turnstile, or media flows.
- [ ] Then set `NACS_PROD_WAF_CDN_VERIFIED=true`.

## Privileged access review
- [ ] Production administrators and service accounts are inventoried.
- [ ] Unneeded privilege is removed.
- [ ] Privileged 2FA readiness is reviewed.
- [ ] Then set `NACS_PROD_ACCESS_REVIEW_VERIFIED=true`.

## Independent VAPT
- [ ] Production or production-equivalent scope is defined.
- [ ] Independent vulnerability assessment / penetration testing is completed.
- [ ] Critical/high findings are remediated or formally accepted before go-live.
- [ ] Then set `NACS_PROD_VAPT_VERIFIED=true`.
