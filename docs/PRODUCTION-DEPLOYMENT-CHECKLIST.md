# NACS-Phil Production Deployment Checklist

This file prepares the finished local Laravel project for later online deployment.
It does not contain passwords, API keys, school applicant data, or hosting credentials.

## Before deployment
- Complete Phase 10 with all tests passing.
- Manually review Home, About, Programs, Admissions, News, Events, Gallery, Contact, Privacy, Admin, and Admissions tracking on phone/tablet/desktop.
- Verify official school name, address, email, phone, school year, admissions wording, and privacy wording.
- Confirm every published student photograph has school authorization and appropriate consent.
- Back up the local database and both public/private storage.
- Commit only safe source code to GitHub.

## Production environment
Create a private production `.env` on the hosting server. Do not commit it.

Production values should include:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-final-domain`
- a production `APP_KEY`
- production database credentials
- production session/cache settings appropriate to the host

## Server requirements
The selected host must support the PHP/Laravel version used by this project, required PHP extensions, Composer, a production database, HTTPS, writable Laravel storage/cache directories, and the ability to point the website document root to Laravel's `public` directory.

## Deployment sequence
1. Create a full backup/checkpoint.
2. Clone or upload the approved GitHub source.
3. Configure the private production `.env`.
4. Install production PHP dependencies.
5. Build or upload approved Vite assets.
6. Configure the production database.
7. Run Laravel migrations.
8. Transfer approved public uploads.
9. Transfer private admissions documents only through a secure server-to-server/admin process.
10. Ensure Laravel storage/framework and bootstrap/cache are writable.
11. Point the domain document root to `/public`.
12. Enable HTTPS.
13. Set `APP_DEBUG=false`.
14. Clear/cache production Laravel configuration/routes/views as appropriate.
15. Re-test public pages, admin login, inquiries, Gallery consent protection, and Admissions access controls.
16. Keep the local project as the development copy. Test future changes locally before deploying them.

## Never publish
- `.env`
- local SQLite databases
- `.nacs-backups`
- private admissions documents
- logs
- private staff/applicant records
- passwords or access codes

## After launch
Maintain regular backups of:
- production database
- `storage/app/public`
- `storage/app/private`
- production environment configuration stored securely outside the public web root

The live website should reflect the same approved Laravel/Blade/CSS/JS GUI as the local project. Hosting changes the environment and URL; it should not require redesigning the website.