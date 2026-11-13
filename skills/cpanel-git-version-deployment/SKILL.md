---
name: cpanel-git-version-deployment
description: Prepare, update, and troubleshoot this Laravel application's production deployment through cPanel Git Version Control. Use when changing the cPanel deployment workflow, preparing a release, diagnosing Deploy HEAD Commit failures, or guiding a cPanel update.
---

# cPanel Git Version Deployment

Follow this skill for this repository only. The production application lives at `/home/mamikhev/public_html/mamikhevan`; the primary domain document root is `/home/mamikhev/public_html`. The Git repository can be checked out elsewhere. The deployment source of truth is the root `.cpanel.yml`.

## Deployment model

- Build the production runtime locally with `powershell -NoProfile -ExecutionPolicy Bypass -File .\prepare-cpanel-deploy.ps1`. This builds Vite assets and creates `cpanel-runtime-no-terminal.zip` with production `vendor/` and `public/build/`.
- When application code, Composer dependencies, or frontend assets change, rebuild and commit `cpanel-runtime-no-terminal.zip` with the release. A `.cpanel.yml`-only change does not require rebuilding the ZIP. Push the approved release to `origin/main`.
- In cPanel Git Version Control, choose **Update from Remote**, confirm the expected HEAD commit, then choose **Deploy HEAD Commit**.
- The cPanel deployment does not run Composer or npm. It extracts the runtime ZIP into the application directory, copies Laravel source and public assets, checks the server environment, removes stale Laravel provider and configuration caches, runs package discovery and migrations, seeds the initial admin when needed, rebuilds configuration and view caches, links public storage, and updates the primary-domain front controller and rewrite rules.
- Push alone does not deploy. Do not run a production deployment unless the user explicitly asks for it.

## Before preparing a release

1. Inspect `git status --short --branch`, the current diff, `.cpanel.yml`, `prepare-cpanel-deploy.ps1`, and `DEPLOYMENT_CPANEL.md`. Keep local agent files, `.env`, and credentials out of the release.
2. Check the PHP requirement in `composer.json`, then run the relevant application tests and Composer validation. For behavior changes, follow the repository test instructions in `AGENTS.md`.
3. If Composer or npm dependencies changed, install them locally from the lockfiles before packaging. Run `prepare-cpanel-deploy.ps1` from the repository root.
4. Verify that the script completes successfully and that the runtime ZIP contains `vendor/autoload.php` and `public/build/manifest.json` plus every asset referenced by the manifest. Confirm production dependencies do not include `vendor/laravel/boost/`. The ZIP must not contain `.env`, application source, or `storage/`.
5. Review `git diff --check` and the staged diff. Commit only files intended for this release, including the refreshed runtime ZIP.

## Production `.env` preflight

The server `.env` is outside Git and must remain in `/home/mamikhev/public_html/mamikhevan/.env`. Never replace it with the local `.env`, commit it, or ask the user to paste secret values into chat. Edit it through cPanel File Manager or the user's approved server access.

Before starting **Deploy HEAD Commit**, confirm all of these entries are present and valid. Set the site's real HTTPS domain and real cPanel database and mail credentials; the examples below are placeholders, not secrets to copy verbatim.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://shop.example.com
APP_KEY=base64:existing-production-key
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_database
DB_USERNAME=cpanel_database_user
DB_PASSWORD=replace-with-database-password
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync
ADMIN_PASSWORD=replace-with-a-strong-initial-admin-password
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.example.com
MAIL_PORT=465
MAIL_USERNAME=shop@example.com
MAIL_PASSWORD=replace-with-mail-password
MAIL_FROM_ADDRESS=shop@example.com
```

`APP_URL` may be quoted or unquoted and must use `https://` without the `/mamikhevan` application path. `APP_KEY` must stay the same after customers and encrypted data exist; never generate a new production key to fix an unrelated deploy error. `ADMIN_PASSWORD` must be nonempty and valid dotenv syntax because `.cpanel.yml` checks it and `AdminUserSeeder` needs it when the configured initial administrator does not exist. The seeder does not overwrite an existing admin password. `QUEUE_CONNECTION=sync` ensures queued email notifications are delivered during requests on shared hosting without a queue worker. Keep the SMTP values valid so customer and admin notifications can be sent.

Also confirm the cPanel CLI PHP is version 8.4.1 or newer and has `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, and `zip`, including the `ZipArchive` class. The deploy script selects a PHP 8.4 CLI when available and stops with a message if a requirement is missing.

## Updating through cPanel

1. Commit and push the approved release to `origin/main`.
2. In cPanel **Git Version Control**, run **Update from Remote**.
3. Confirm the checkout is on the intended branch and HEAD is the expected commit. The checkout must include the root `.cpanel.yml` and must be clean enough for cPanel to enable deployment.
4. Select **Deploy HEAD Commit** and read the deployment log from the first failed command onward.
5. After a successful deploy, open the production site and check the changed customer/admin flows, email delivery, and Laravel logs under `storage/logs/`.

Back up the production database before deploying a release with migrations. Do not run `migrate:fresh`, seed the full demo `DatabaseSeeder`, or delete production data. The deploy script runs `migrate --force` and only the idempotent `AdminUserSeeder`.

## Troubleshooting

- If the log stops at a `grep` check for `.env`, the failure is in the server's environment file, not the Git checkout. Add or correct all required values together, then retry **Deploy HEAD Commit**. The script stops at the first failed check, so expect later checks to appear only after earlier ones pass.
- `APP_URL` must be an HTTPS URL; quoted values such as `APP_URL="https://shop.example.com"` are accepted by the current validator. `SESSION_SECURE_COOKIE` must be exactly `true`, and `QUEUE_CONNECTION` exactly `sync`.
- Keep the production `.env` in `/home/mamikhev/public_html/mamikhevan/.env`. Never copy `.env.example` over it, make `.cpanel.yml` create it, or overwrite it during deployment. Set production values through cPanel File Manager. Do not ask the user to paste secrets into chat.
- If the log reports missing PHP extensions or `ZipArchive`, enable them for the PHP CLI version selected by cPanel. The website's PHP version and the terminal/deployment CLI PHP can differ.
- If the deployment log prints `HASH(0x...)` as a shell command and reports a shell syntax error, inspect the most recently executed `.cpanel.yml` task. cPanel can misinterpret unquoted inline PHP arrays or braces as YAML objects. Put complex shell tasks in a folded YAML scalar (`>-`) and keep PHP snippets simple. Confirm the fix is in the deployed HEAD before retrying.
- If Artisan reports `Class "Laravel\\Boost\\BoostServiceProvider" not found`, the production runtime likely has a stale `bootstrap/cache/packages.php` or `services.php` that refers to the development-only Boost package. The current deploy removes those generated manifests before `package:discover`; ensure cPanel has the commit containing that step. Do not add Boost to production dependencies just to hide a stale manifest.
- If `AdminUserSeeder` reports `Set ADMIN_PASSWORD before creating the initial administrator account` even though the raw `.env` check passed, an old `bootstrap/cache/config.php` may be hiding the current environment value. The deploy must run `artisan config:clear --no-ansi` before package discovery, migrations, and seeding, then run `config:cache` after seeding. Confirm cPanel is deploying the commit with this order. If it still fails, use File Manager to verify the value is actually nonempty and valid dotenv syntax (not whitespace, an empty quoted value, or a value consumed as a comment). Never print or share the password. The seeder only needs it if the user for `shop.admin.email` does not yet exist.
- If Vite fails locally with `spawn EPERM`, rerun `npm.cmd run build` in an environment that allows child process creation, then rerun `prepare-cpanel-deploy.ps1` and verify the generated ZIP. Do not commit a partial candidate ZIP; check the archive can be listed and includes the manifest and every referenced asset before replacing the release ZIP.
- The production ZIP is assembled with `composer install --no-dev`; it intentionally omits development tools such as Laravel Boost. When only `.cpanel.yml` changes, update the Git checkout but do not rebuild the runtime ZIP unnecessarily.
- If `unzip` is unavailable, extract `cpanel-runtime-no-terminal.zip` through File Manager directly into `/home/mamikhev/public_html/mamikhevan`, so `vendor/` and `public/build/` are at the application root, then rerun deployment.
- When deployment fails after migrations, read the first actual failing command. Migrations may already have completed; do not roll back or rerun destructive database commands as a shortcut. Correct the reported issue and retry the idempotent deployment flow.
- If deployment succeeds but the domain does not resolve, investigate DNS separately. The apex domain needs an `A` record to the account's cPanel shared/dedicated IP; `www` must point to the apex or the same server. A DNS issue does not mean the Git deployment failed.
- For a failed Deploy HEAD Commit, inspect the newest `vc_<timestamp>_git_deploy.log` in `/home/mamikhev/.cpanel/logs/` and the Laravel log in the application `storage/logs/`. Request only the relevant error lines; never request the contents of `.env` or secrets.
- Do not use `git clean`, bulk delete files, or overwrite production `.env`/`storage/` as a troubleshooting shortcut.
