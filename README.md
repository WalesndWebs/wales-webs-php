# Wales & Webs — portable PHP site

The active app is PHP 8.2+, server-rendered HTML, CSS, vanilla JavaScript and SQL.
No React, Node.js, build system or Composer dependencies are needed to run this export.

## What is included

- Shared public header with an orbiting star and only Tech Stories, Our Services,
  Our Work, Contact Us and Usabime. Both story/work menus open the Journal.
- Public Journal and individual stories; private draft/publish/edit/delete studio.
- Contact form stored in a private inbox, with review status and internal notes.
- Assisted Usabime onboarding, private review, custom currency/decimal pricing,
  saved proposals and browser Print / Save PDF.
- No automatic emails, customer accounts, checkout or direct asset uploads.
- PostgreSQL and MySQL schemas; the GitHub repository also includes published
  Journal stories as optional starter SQL, without private customer records.

## GitHub repository

This repository contains only the finished PHP application. It does not include
the old React/Node sources, Replit workspace tools, passwords, live database
credentials, unpublished drafts, private customer requests or proposals.
Your full current database can be moved separately using the private export
described below. Never commit that private backup.

GitHub stores the source; **GitHub Pages cannot run this PHP application**.
Deploy it to a PHP-capable host with a PostgreSQL or MySQL database.

From any signed-in private page, **Export PHP + SQL** downloads a fresh ZIP
containing the complete PHP application and both matching SQL data exports.
It never includes your password, populated local configuration or login sessions.
The download contains private customer records: store it securely. Enable the
PHP zip extension to use this button (included in the Replit PHP runtime).

## Hosting

Use PHP 8.2 or newer with PDO and **pdo_pgsql** (PostgreSQL) or **pdo_mysql**
(MySQL 8.0.13+/MariaDB 10.6+). Enable mbstring and PHP sessions.

1. Extract the archive into a private application folder.
2. Set the web document root to `public/` — not the application's parent folder.
   Everything in `app/`, `database/`, `cli/` and the configuration stays private.
3. Create an empty database and import `database/schema-postgresql.sql` OR
   `database/schema-mysql.sql`, matching the database on your host.
   - Fresh installation from GitHub: optionally import the matching
     `database/published-stories.postgresql.sql` or `published-stories.mysql.sql`
     to include the existing public Journal stories.
   - Moving all existing records: obtain **Export PHP + SQL** from the current
     private workspace and import its matching `database/current-data.*.sql`
     instead of the published-story seed. Do not import both.
   Private exports contain actual customer records; never upload them into a public
   folder or commit them to GitHub. Import them only into an empty database;
   they do not erase or overwrite existing records. Rate-limit history and login
   sessions are not exported.
4. Set environment variables `DATABASE_URL` for PostgreSQL, or `DB_DSN`,
   `DB_USER`, `DB_PASSWORD` for either database. Set `BLOG_ADMIN_PASSWORD`.
   Use a long unique password. Never include credentials in URLs or public files.
   If your host cannot supply environment variables, copy `config.example.php`
   to `config.local.php`, fill it privately, and do not commit it.
5. Set `BASE_PATH=/` (or the subfolder prefix when mounting below the domain root).
6. Apache: enable mod_rewrite and AllowOverride so `public/.htaccess` can route pages.
   nginx/PHP-FPM: point the root to `public/` and use
   `try_files $uri $uri/ /index.php?$query_string;`.
7. Use HTTPS in production. PHP must see `HTTPS=on` or a trustworthy
   `X-Forwarded-Proto: https` from your reverse proxy so cookies are secure.
8. Keep the PHP session directory writable and shared between workers.
   Multiple independent hosts need shared session storage or sticky routing.

The supplied `bin/serve.sh` is the Replit production/development entrypoint:
it runs nginx with PHP-FPM, using `PORT`, with no source or SQL files exposed.
nginx and PHP-FPM must be installed to use this script outside Replit.

For **local development only**, you can run:

```
php -S 127.0.0.1:8000 -t public router.php
```

If the database or password is missing, the application fails explicitly. It
never replaces customer data with fake records or silently opens the admin.
Schemas are not run automatically on startup. Import a schema once when
setting up a new host. On Replit, Publish applies the development database
schema changes through its database publishing flow.

## Private pages (save these URLs yourself)

- `/usabime/admin` — request review and proposals
- `/admin/contact` — customer contact messages
- `/journal/studio` — blog management

No admin links are exposed in public menus or footers. Hiding links is not
the security boundary: all private pages require an authenticated session.
Sessions expire after 30 minutes idle, and changing the configured password
invalidates existing sessions. Forms use CSRF protection, prepared database
queries and submission/sign-in rate limits. All story/message text is escaped.

The contact inbox's **Open an email draft** action only opens your own email
app. You decide what to write and send. Mark a message “Replied” after sending.
Print / Save PDF saves the proposal first; the print sheet excludes private
notes, contact inbox messages, navigation and authoring controls.

## Checks and fresh exports

```
php cli/lint.php
php tests/unit.php
php cli/export.php --dialect=postgresql --output=/private/path/backup.sql
php cli/export.php --dialect=mysql --output=/private/path/backup-mysql.sql
php cli/export-public-stories.php --dialect=postgresql --output=database/published-stories.postgresql.sql
php cli/export-public-stories.php --dialect=mysql --output=database/published-stories.mysql.sql
```

Exports contain customer information but no password or database credentials.
Keep backups private and regenerate them whenever you move newer records.
The MySQL schema/export is supplied for other hosts; the running Replit app
continues to use the original PostgreSQL database.