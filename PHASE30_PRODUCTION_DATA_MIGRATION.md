# NACS-Phil Phase 30 - Production Data Migration Readiness

Phase 30 prevents a common launch mistake: creating an empty production MySQL database while real school content still lives only in the local SQLite database, or copying private family/admissions data without an approved handling plan.

## Safe audit

Run:

```text
php artisan nacs:data-audit
php artisan nacs:data-audit --json
php artisan nacs:data-audit --strict
```

The audit is read-only. It reports table names, record counts, and file counts only. It does not print family names, email addresses, phone numbers, access codes, staff passwords, inquiry messages, document names, or file contents.

## Data classes

The audit separates data into:

- public CMS/content
- staff/admin accounts
- private family/admissions records
- audit records
- runtime-only Laravel tables
- unknown tables requiring manual classification

Private admissions documents are counted separately from public media.

## Required decision record

Copy:

```text
PRODUCTION_DATA_MIGRATION_DECISION.example.json
```

to the local-only file:

```text
.nacs-data-migration-decision.json
```

The actual decision file is ignored by Git. Set an item to `true` only after that decision has genuinely been reviewed.

The strict audit remains blocked until all decisions are complete and no database table is left unclassified.

## Production database rule

Use the production host's MySQL or MariaDB database. Do not publish or serve the local SQLite file.

Do not commit:

- SQLite databases
- SQL dumps
- CSV exports containing family/student data
- admissions document archives
- `.env`
- passwords, access codes, API keys, Turnstile secrets, SMTP credentials

## Transfer strategy

Before importing anything, choose one of these deliberately:

1. **Fresh production start** - migrate schema and manually recreate only approved public content/admin accounts.
2. **Controlled content transfer** - transfer approved public CMS content while excluding private test records.
3. **Controlled full transfer** - transfer approved public, staff, and private records through a confidential process with backup, access control, and post-import verification.

Never use GitHub as the transfer channel for private data.

## Required proof before Phase 34

Keep the local decision record complete, verify the target database independently, and retain a private backup before any production import.
