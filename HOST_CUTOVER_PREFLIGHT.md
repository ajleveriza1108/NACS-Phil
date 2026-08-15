# NACS-Phil Host Capability & Cutover Preflight — Phase 26

Phase 26 is intentionally hosting-neutral. It does not guess a cPanel username, domain, server path, database name, DNS provider, or hosting vendor.

The purpose is to test a candidate host **before** NACS-Phil is cut over to real families and staff.

## Commands

After the code and PHP dependencies are available on a candidate server, run:

```bash
php artisan nacs:host-check
```

For an enforceable result:

```bash
php artisan nacs:host-check --strict
```

For a machine-readable report that does not expose `.env` secrets:

```bash
php artisan nacs:host-check --json
```

After the real production `.env` is configured, the final server gate is:

```bash
php artisan nacs:production-check --strict
```

Both strict commands must pass before live cutover.

## What the host check verifies

Required:

- PHP 8.4.1 or newer.
- Core PHP extensions used by the application.
- The PDO driver matching the configured database connection.
- `upload_max_filesize` can accept the application's requested admissions files up to 5 MB.
- `post_max_size` has enough overhead for a 5 MB upload.
- Laravel `storage`, `bootstrap/cache`, private storage, and public media storage are writable.
- `public/index.php` exists.
- the production Vite build manifest exists.

Advisory:

- at least 128 MB PHP memory.
- a working public-storage symlink or hosting-provider equivalent.
- shell/process access for convenient deployment.

A host can still be usable when an advisory check warns. A required BLOCK must be resolved.

## Document root

The live web document root must point to Laravel's `public` directory.

Do not expose the repository root, `.env`, `storage`, `vendor`, database files, deployment documents, or source files through the public web root.

If a shared host cannot point the domain directly at Laravel's `public` directory, do not improvise by copying private application files into `public_html`. Use the provider's documented Laravel deployment method or choose a host that supports a safe document-root arrangement.

## Candidate-host workflow

1. Create a temporary/staging location or subdomain on the candidate host.
2. Deploy the tested NACS-Phil commit and production dependencies/build assets.
3. Keep the staging site unavailable to the public or protect it while configuring.
4. Run `php artisan nacs:host-check --strict`.
5. Create/configure the production MySQL/MariaDB database.
6. Create the private production `.env` from `PRODUCTION_ENV_TEMPLATE.txt`.
7. Generate the production APP_KEY.
8. Run migrations.
9. Configure persistent public/private storage.
10. Run `php artisan nacs:production-check --strict`.
11. Test Admin login/2FA, Admissions, Gallery, Documents, and Facebook Live & Videos.
12. Complete School Manager → Launch Readiness.
13. Only then point the real domain/DNS at the production site.

## Upload-size requirement

The Admissions Portal currently accepts requested PDF/JPG/PNG documents with a maximum application limit of 5 MB per file.

The server should therefore use at least:

```ini
upload_max_filesize = 5M
post_max_size = 6M
```

A slightly larger POST limit such as 8M is reasonable if the provider allows it.

Do not increase the application's admissions upload limit simply because the server permits larger files.

## Database driver

Normal shared-hosting production is expected to use MySQL/MariaDB.

When `DB_CONNECTION=mysql` or `mariadb`, the host needs PDO MySQL support.

The host check reads only the configured connection name and the list of available PDO drivers; it does not print database passwords.

## Build assets

The public production build must contain:

```text
public/build/manifest.json
```

If Node is unavailable on the host, create the build in a trusted environment using the locked project dependencies and deploy the resulting tested `public/build` output according to the provider's workflow.

## Storage

Required persistent writable locations include:

```text
storage/
bootstrap/cache/
storage/app/private/
storage/app/public/
```

Admissions/private files must stay outside the web-accessible public tree.

The Gallery and other authorized public media may use Laravel's `public` storage disk and its public storage link.

## Host rejection conditions

Do not use a host for the live school site if required blockers cannot be resolved, especially:

- PHP older than 8.4.1.
- missing required PHP extensions.
- no PDO driver for the production database.
- inability to keep `.env` and private storage outside the public web root.
- no writable Laravel runtime storage.
- upload limits below the application's 5 MB admissions requirement.
- no practical HTTPS support.

## Phase 27

Once the actual provider and domain are known, Phase 27 can be provider-specific: exact document-root path, SSL/DNS steps, database creation, SSH/cPanel commands, deployment directory, backups, storage persistence, and final live cutover.
