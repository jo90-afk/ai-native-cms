# Installation, upgrade, backup, and rollback

This document describes portable AI Native CMS installation and database-state handling. It deliberately does not prescribe a hosting provider, repository updater, web-server control panel, or production deployment mechanism.

The current internal release candidate is `0.1.0-rc.1` with schema version 7.

## Runtime requirements

- PHP 8.1 or newer
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- a writable public site tree only for files the configured CMS projects or uploads
- Python 3.10+ only for repository/release validation; Python is not a public-site runtime dependency

Production CMS access requires HTTPS. A non-local MySQL connection requires verified TLS by default unless the operator deliberately configures the explicit insecure private-network exception.

## 1. Prepare adopter configuration

Copy `config/site.example.php` to `config/site.php` and edit only adopter-owned public structure: site identity, public origin, repository pages/documents, writing paths, media roots, navigation defaults, allowed branding tokens, projection outputs/hooks, and optional readiness adapters.

`config/site.php` is local adopter state and is intentionally excluded from release-candidate packages.

Do not put credentials or secret tokens in site configuration.

## 2. Prepare private runtime configuration

Use environment variables or copy `database/private-config.example.ini` to a private path outside the public site root. The conventional shared-hosting path is `~/.ai-native-cms.ini`; `AINCMS_SECRET_CONFIG_FILE` may point to another private path.

Configure at minimum:

- `AINCMS_ENV=production`
- `AINCMS_PUBLIC_ORIGIN=https://...`
- `AINCMS_DB_HOST` or `AINCMS_DB_SOCKET`
- `AINCMS_DB_NAME`
- `AINCMS_DB_USER`
- `AINCMS_DB_PASSWORD`
- `AINCMS_CMS_ENABLED=1`
- `AINCMS_CMS_USER`
- `AINCMS_CMS_PASSWORD_HASH`
- `AINCMS_RATE_LIMIT_SECRET`

The runtime refuses a private INI file that resolves inside the public site root.

Generate the bootstrap password hash with the PHP version that will run the application, for example:

```bash
php -r 'echo password_hash("replace-this-password", defined("PASSWORD_ARGON2ID") ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT), PHP_EOL;'
```

Do not commit the resulting populated private configuration.

## 3. Back up before changing an existing database

For a fresh empty database, continue to bootstrap below.

Before any operation against an existing AI Native CMS database, create a full database backup using the database server or hosting platform’s supported backup mechanism. A backup is not considered verified until it has been restored into a disposable database and the restored schema/table counts have been inspected.

At minimum record alongside the backup:

- source application version;
- source schema version from `app_meta`;
- backup timestamp;
- database identifier/environment;
- the Git/source revision of the application code running against it.

Do not store database dumps inside the public site tree or release package.

## 4. Initialize schema and first owner

Create a dedicated empty database and run:

```bash
php database/bootstrap.php
```

Bootstrap is CLI-only and idempotent. It derives the required table set and current schema version from `database/schema.sql`, installs structure plus the first persisted CMS owner, and does not seed adopter content.

Bootstrap fails closed when:

- the database is non-empty but does not resemble AI Native CMS;
- a partial installation exists and `--repair` was not explicitly supplied;
- a partial installation is stamped with an older schema version;
- configured bootstrap owner credentials are invalid;
- the bootstrap lock cannot be acquired.

A repair is permitted only for an incomplete installation already stamped with the repository’s current schema version:

```bash
php database/bootstrap.php --repair
```

`--repair` is not a migration command. It must never be used to convert an older schema version into a newer one.

## 5. Initialize canonical repository content

After schema/owner bootstrap succeeds, explicitly accept the repository page/document baseline into canonical SQL:

```bash
php database/reconcile.php initial-import
```

This is separate from bootstrap by design. Schema creation does not silently turn repository files into accepted authored database state.

Subsequent repository changes should use the same reconciliation command with an appropriate source reference rather than overwriting canonical SQL directly.

## 6. Run readiness diagnostics

Run the portable read-only report:

```bash
php database/readiness.php
```

After CMS login, the same model is available under **Readiness** in `/cms/`.

Blocking failures should be resolved before production use. Warnings are deliberately non-blocking conditions that require operator judgment, such as inability to read MySQL grant metadata, retained bootstrap password material after owner persistence, or explicitly trusted proxy/network exceptions.

Readiness does not initialize or migrate schema, publish content, send email, deploy files, or expose password/grant values.

## 7. First login and bootstrap credential retirement

Open `/cms/` directly over HTTPS and authenticate as the configured owner. Once the owner exists in `cms_users`, the environment/bootstrap identity is no longer a permanent fallback account.

After verifying persisted-owner login and storing recovery procedures appropriate to the deployment, remove `AINCMS_CMS_PASSWORD_HASH` from the private runtime configuration when practical. Readiness warns when a persisted owner exists while the initializer hash remains configured.

## Upgrade and migration policy

The `0.1.0-rc.1` candidate establishes schema version 7 as its fresh-install baseline. It does **not** ship an in-place migration from earlier private extraction snapshots.

For any database whose `app_meta.schema_version` is lower than the repository schema version:

1. do not run `database/bootstrap.php --repair`;
2. take and restore-test a full backup;
3. keep the older application code paired with that database until an explicit versioned migration for the source schema exists;
4. apply only a reviewed migration whose declared source and target schema versions match the database and candidate;
5. run `php database/readiness.php` and the full application validation appropriate to the deployment before allowing writes.

Future migrations should be versioned, idempotent where practical, fail when the source version is outside their declared range, and advance `app_meta.schema_version` only after their structural changes succeed.

## Rollback policy

Code and canonical database state form one release boundary once a newer version has accepted writes.

For a failed fresh installation with no accepted content, discard the dedicated database and retry from a clean empty database after correcting configuration.

For a failed upgrade/migration:

1. stop CMS writes;
2. restore the verified pre-upgrade database backup;
3. restore the exact application source revision/version that was paired with that backup;
4. rerun readiness against the restored pair;
5. verify representative public projections before reopening authoring.

Do not roll back only PHP/files after a newer schema has accepted authored writes unless the migration documentation explicitly states backward compatibility.

## Deployment boundary

This repository does not currently choose how an adopter deploys files to a host. FTP/SFTP, SSH/Git checkout, CI deployment, control-panel upload, container/image distribution, and provider-specific repository updaters are deployment adapters outside portable core.

A successful release-candidate build or readiness report is evidence about the candidate, not authorization to deploy it to production.
