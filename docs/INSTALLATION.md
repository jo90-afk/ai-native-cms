# Installation, upgrade, backup, and rollback

This document describes portable AI Native CMS installation and database-state handling. It deliberately does not prescribe a hosting provider, repository updater, web-server control panel, or production deployment mechanism.

The current internal release candidate is `0.1.0-rc.2` with schema version 8.

## Runtime requirements

- PHP 8.1 or newer
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- a writable public site tree only for files the configured CMS projects or uploads
- Python 3.10+ only for repository/release validation

Production CMS access requires HTTPS. A non-local MySQL connection requires verified TLS by default unless the operator deliberately configures the explicit insecure private-network exception.

## Fresh installation

1. Copy `config/site.example.php` to `config/site.php` and configure adopter-owned public structure. Redirect `system_aliases`, if any, are repository-owned read-only compatibility routes; manual and post-history redirects live in MySQL.
2. Configure private `AINCMS_*` values through environment variables or an INI file outside the public root. Do not commit populated credentials.
3. For a dedicated empty database run:

```bash
php database/bootstrap.php
```

Bootstrap is CLI-only and idempotent. It derives the current schema/table set from `database/schema.sql`, installs structure plus the first owner, and never seeds adopter content. `--repair` is limited to incomplete installs already stamped with the current schema. It is not a migration engine.

4. Initialize canonical repository content separately:

```bash
php database/reconcile.php initial-import
```

5. Run the read-only readiness report:

```bash
php database/readiness.php
```

6. Open `/cms/` over HTTPS, verify persisted-owner login, and remove the bootstrap password hash from private configuration when practical.

## Back up before changing an existing database

Before any migration or upgrade, create a full database backup using the database server or hosting platform’s supported mechanism. A backup is not verified until it has been restored into a disposable database and its schema/table counts have been inspected.

Record with the backup: source application version, `app_meta.schema_version`, timestamp/environment, and the exact source revision paired with the database. Never place database dumps inside the public site tree or release package.

## Schema 7 → 8 upgrade

`0.1.0-rc.1` used schema 7. `0.1.0-rc.2` uses schema 8 and adds canonical redirect authority. Do **not** use `database/bootstrap.php --repair` for this transition.

After taking and restore-testing the backup, inspect the migration without applying it:

```bash
php database/migrations/7-to-8.php
```

The command reports the current state and requires the explicit apply flag. Apply only to a database whose schema version is exactly 7:

```bash
php database/migrations/7-to-8.php --apply
```

The migration acquires a database lock, creates `redirect_records` idempotently, then advances `app_meta.schema_version` from 7 to 8. It refuses other source versions. If a database is already stamped v8 but the redirect table is absent, it fails rather than guessing at repair.

After migration run:

```bash
php database/readiness.php
php database/reconcile.php post-migration
```

Reconciliation remains a CLI/deployment boundary. Browser CMS pages cannot import Git/source candidates or run migrations.

## Redirect runtime

Canonical manual and post-history redirect records are projected to `__redirect-map.php`. `__redirect.php` consumes only that generated map; it never opens MySQL. A deployment must explicitly route unresolved public paths to that runtime (or implement equivalent behavior in another server adapter). The portable core does not assume Apache, Nginx, a CDN, or a particular hosting provider.

## Future migration policy

For any database whose schema version is lower than the repository schema version:

1. do not use bootstrap repair as an upgrade mechanism;
2. take and restore-test a backup;
3. keep the older code paired with the older database until a versioned migration exists;
4. apply only a migration whose declared source version matches;
5. run readiness, reconciliation when appropriate, and the full validation gate before reopening writes.

Migrations should be versioned, idempotent where practical, fail outside their declared source range, and advance `app_meta.schema_version` only after required structural changes succeed.

## Rollback policy

Code and canonical database state form one release boundary once a newer schema has accepted writes. For a failed schema 7→8 upgrade:

1. stop CMS writes;
2. restore the verified pre-migration database backup rather than dropping redirect state manually;
3. restore the exact schema-7 application revision/version paired with that backup;
4. rerun readiness against the restored pair;
5. verify representative public projections before reopening authoring.

Do not roll back only PHP/files after schema 8 has accepted authored redirect or other canonical writes.

For a failed fresh installation with no accepted content, discard the dedicated database and retry from a clean empty database after correcting configuration.

## Deployment boundary

This repository does not choose how an adopter deploys files or intercepts unresolved routes. FTP/SFTP, SSH/Git checkout, CI deployment, control-panel upload, containers, web-server rewrite policy, and provider-specific repository updaters are deployment adapters outside portable core.

A successful release-candidate build or readiness report is evidence about the candidate, not authorization to deploy or publish it.
