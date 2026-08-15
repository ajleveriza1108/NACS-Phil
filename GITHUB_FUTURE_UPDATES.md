# NACS-Phil GitHub Future Updates

## Goal

Future application updates should follow this controlled path:

```text
local development / ChatGPT / Codex
        |
        v
agent/* branch
        |
        v
draft pull request
        |
        v
GitHub Quality Gate
        |
        v
review and approval
        |
        v
main
        |
        v
production deployment (enabled only after the real host is configured)
```

Routine school content edited in the NACS-Phil Admin remains production data and is not replaced by Git deployment.

## Source of truth

- `main` is the intended production source branch.
- Development work belongs on `agent/*` or another approved feature branch.
- Do not deploy a feature branch directly to production.
- Do not force-push production history.
- Pull requests are the normal path into `main`.

## Automatic quality gate

`.github/workflows/quality-gate.yml` runs for pull requests targeting `main`,
pushes to `main`, and manual workflow runs.

The gate installs PHP and Node dependencies in a fresh GitHub runner, creates a
temporary CI SQLite database, builds Vite assets, runs the strict NACS functional
check, runs the complete Laravel test suite, checks whitespace, and verifies CI
did not modify tracked source.

The current locked PHP dependency set includes Symfony 8.1 packages that require
PHP 8.4.1 or newer. The repository, host preflight, and GitHub Quality Gate
therefore use PHP 8.4.1+ as the supported runtime baseline.

GitHub CI is an additional release gate. It does not replace browser/device,
staging, host, privacy, backup, or production checks.

## Production auto-deployment

Production auto-deployment is deliberately NOT enabled yet.

The repository contains:

`.github/deployment-templates/production-deploy.yml`

This is a non-executable template. Keep it outside `.github/workflows/` until the
actual hosting provider and deployment method are known.

Before enabling automatic production deployment, confirm:

1. supported PHP/Laravel runtime
2. document root points to `public`
3. authenticated SSH/provider Git deployment path
4. least-privilege access to the private repository
5. server `.env` persists and Git never overwrites it
6. production database and upload storage persist across deployments
7. private admissions storage remains outside the public web root
8. backup/rollback exists before migrations
9. rollback has been tested
10. staging passes before production auto-deployment is enabled

## Secrets

Never commit deployment credentials. Use GitHub Environment secrets/variables
or the hosting provider's encrypted secret store.

Do not commit APP_KEY, database passwords, SMTP credentials, Turnstile secrets,
admin passwords, private student/family data, admissions documents, or backups.

## What Git updates

Git deployment is for application source such as Laravel code, Blade templates,
CSS/JavaScript, tests, deployment templates, and intentionally tracked public assets.

Git deployment must not overwrite:

- production `.env`
- production database contents
- uploaded Gallery/Media files
- private admissions files
- Laravel logs
- runtime sessions/cache
- backups

## Future code update procedure

1. start from the latest tested `main`
2. create an `agent/*` branch
3. make the smallest required source change
4. run focused tests and complete local QA
5. push the branch
6. open a draft pull request
7. wait for the GitHub Quality Gate
8. perform required visual/manual QA
9. merge only after approval
10. once host auto-deployment is configured, the approved `main` update may deploy automatically

## Emergency rule

Do not casually edit production source and later overwrite it from GitHub.
If an emergency host-side edit is unavoidable, reconcile it into source control
before the next normal deployment.
