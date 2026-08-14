# NACS-Phil Phase 33 - Final Production Security and Recovery Hardening

Phase 33 adds a restrictive Content Security Policy while preserving the two approved external browser services:

- Cloudflare Turnstile challenge script/frame/network origin
- Facebook video iframe origin

The application keeps local scripts/fonts/assets on `'self'`, blocks plugins/objects, limits form destinations, restricts framing, and upgrades insecure requests only on secure production requests.

## Security command

```text
php artisan nacs:recovery-check
php artisan nacs:recovery-check --strict
```

The command checks:

- no database dumps, private keys, backup archives, logs, or private admissions paths are under `public`
- private/admissions storage resolves outside public
- the hardened CSP remains present
- a real backup-and-restore verification record exists

## Restore verification

Copy:

```text
RECOVERY_VERIFICATION.example.json
```

to:

```text
.nacs-recovery-verification.json
```

The real file is ignored by Git.

Do not mark a restore item true merely because a backup job says "successful." Restore into an isolated location/database and verify that the restored database and files can actually be opened.

## Backup scope

A production backup plan must include:

- MySQL/MariaDB database
- `storage/app/private`
- `storage/app/admissions`
- `storage/app/public` or equivalent public media storage
- production `.env`/APP_KEY through a secure credential backup process, never GitHub

Keep at least one protected copy outside the live hosting account.

## Security header maintenance

If an additional external script/frame service is introduced later, do not weaken CSP to `https:` or `*` for scripts/frames. Add only the exact required origin after reviewing that service.
