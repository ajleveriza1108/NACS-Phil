# NACS-Phil Backup and Restore Runbook

This runbook covers the school website/CMS, not a full Student Information System.

## What must be backed up

1. Production database.
2. `storage/app/public` approved public media.
3. `storage/app/private` private school documents and other protected files.
4. `storage/app/admissions` private admissions files when present.
5. Production `.env` or equivalent hosting environment configuration stored in a separate secure secrets location.
6. The exact deployed source revision, which should also exist in GitHub.

Do not put runtime backups inside the Git repository.

## Backup verification
A backup is not considered verified merely because files were copied. Record and compare:

- database file checksum or database export verification result
- relative file paths
- file sizes
- SHA-256 hashes for stored files where practical
- source revision/commit
- backup timestamp

## Local Phase 14 restore drill
The Phase 14 release tool performs a non-destructive local drill:

1. Copies the local SQLite database into `.nacs-backups`.
2. Verifies the database SHA-256 checksum.
3. Copies public/private/admissions storage into the same backup checkpoint.
4. Produces a source manifest for those storage trees.
5. Copies the backup into a separate restore-drill directory.
6. Compares the restored database and storage manifests against the backup.
7. Never overwrites the active local database or active storage during the drill.

## Production restore sequence

1. Put the live site in maintenance mode or otherwise stop writes.
2. Snapshot the failed/current state before replacing anything.
3. Restore the database into a clean target database.
4. Restore public media.
5. Restore private school/admissions storage with the same access restrictions.
6. Restore production environment configuration from the secure secrets location.
7. Deploy the matching source revision.
8. Install the matching production dependencies.
9. Rebuild Laravel caches.
10. Verify Admin authentication and role boundaries.
11. Verify public media and private document download controls.
12. Verify an admissions tracking flow using a disposable test application.
13. Reopen the site only after verification.

## Recovery rule
Never test a restore by overwriting the only working copy. Restore into a separate location/database first and compare before switching production traffic.
