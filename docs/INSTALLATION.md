# Installation, onboarding, upgrade, backup, and rollback

This document describes the portable AI Native CMS installation path and canonical database-state handling. It deliberately does not prescribe a hosting provider, repository updater, control panel, or production deployment mechanism.

The current release candidate is `0.1.0-rc.4` with **schema 10**. The published predecessor `0.1.0-rc.3` remains a frozen schema-8 artifact.

## Runtime requirements

- PHP 8.1 or newer
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- a writable public site tree for files the configured CMS projects or uploads
- Python 3.10+ only for repository/release validation

Production CMS access requires HTTPS. A non-local MySQL connection requires verified TLS by default unless the operator deliberately configures the explicit insecure private-network exception.

## Fresh installation — schema 10

### 1. Name the public site

The release candidate ships a usable neutral Home/About/Writing starter. Create adopter-owned **public, non-secret** site configuration with:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
```

This writes `config/site.php` from the shipped example. It does not ask for, read, or write database or mail credentials. It refuses to overwrite an existing file unless `--force` is explicitly supplied.

### 2. Configure private runtime secrets

Configure required `AINCMS_*` values through environment variables or an INI file outside the public root. Do not commit populated credentials. See `database/private-config.example.ini` and `SECURITY.md`.

Mail-provider configuration is optional unless the site uses Audience confirmation mail. See `docs/CPANEL-EMAIL.md` for the cPanel path; use the exact provider settings shown by the account rather than guessing the SMTP hostname.

### 3. Bootstrap a dedicated empty database

```bash
php database/bootstrap.php
```

For rc.4 this installs the current schema **10** directly, including `block_presets` and the generic Audience tables. Bootstrap is CLI-only and idempotent. `--repair` only completes interrupted installs already stamped with the current schema; it is never a migration engine.

### 4. Initialize canonical repository content

```bash
php database/reconcile.php initial-import
```

Repository pages are reviewed source proposals until this explicit reconciliation establishes canonical SQL lineage. Browser onboarding does not promote Git/source files into authority.

### 5. Sign in and build the site

Open `/cms/` over HTTPS. Incomplete installations land in `/cms/onboarding.php`, a resumable state-derived workspace.

Onboarding guides the adopter through public identity/configuration, database/owner state, canonical content initialization, page composition, reusable blocks, branding/navigation, optional publishing, optional Audience/mail configuration when present, and final readiness evidence. It observes authoritative state; it does not create a parallel model, store credentials, migrate schema, or deploy.

### 6. Run readiness and rebuild

```bash
php database/readiness.php
```

Resolve blocking failures before production use. A normal public rebuild projects canonical content plus SEO, navigation, branding, redirects, clean managed routes, `site-index.json`, XML/text sitemaps, and `llms.txt`. These files are generated delivery/discovery state, not a second authoring authority.

When practical, remove the bootstrap password hash from private configuration after persisted-owner login has been verified.

## Back up before changing an existing database

Before any migration, create a full database backup using the database server or hosting platform’s supported mechanism and prove restore into a disposable database. Pair the backup with the exact application revision, source application version, `app_meta.schema_version`, and timestamp/environment. Never place database dumps in the public tree or release package.

Once a newer schema accepts writes, code and canonical SQL are one rollback boundary.

## Supported explicit migration chain

Inspect each migration without `--apply` first. Apply only when the installed schema exactly matches the migration’s declared source version.

### Schema 7 → 8

```bash
php database/migrations/7-to-8.php
php database/migrations/7-to-8.php --apply
```

This establishes canonical redirect authority and advances schema 7 → 8.

### Schema 8 / `0.1.0-rc.3` → 9

```bash
php database/migrations/8-to-9.php
php database/migrations/8-to-9.php --apply
```

This creates `block_presets`, converts the prior trusted page-block templates, rewrites stored composition references from `templateKey` to `presetKey`, archives the retired template table, and advances schema 8 → 9.

### Schema 9 → 10

```bash
php database/migrations/9-to-10.php
php database/migrations/9-to-10.php --apply
```

This creates `audience_lists` and `audience_subscriptions` and advances schema 9 → 10. If the earlier optional `subscribers` primitive exists, valid list membership is copied into generic Audience authority, imported lists start **disabled**, and the old table is archived. This preserves historical consent state without silently enabling new public collection.

### Upgrading directly from rc.3

`0.1.0-rc.3` is schema 8, so its supported rc.4 path is deliberately sequential:

```bash
php database/migrations/8-to-9.php --apply
php database/migrations/9-to-10.php --apply
php database/readiness.php
php database/reconcile.php post-migration
```

Do not skip a migration or use `database/bootstrap.php --repair` to cross a version boundary.

A schema-7 site runs 7 → 8 → 9 → 10. A schema-9 site runs 9 → 10.

## Audience and email after migration

Migration never configures provider credentials or activates imported lists. After schema 10:

1. open **Audience** in the CMS;
2. review any imported disabled list before enabling collection;
3. configure private `AINCMS_MAIL_*` values if confirmation mail is needed;
4. send one explicit test message;
5. place the server-generated Signup block through Page Composer only when the list should become public.

This sequence keeps migration, credentials, public activation, and deployment as separate operator decisions.

## Redirect and discovery runtimes

Canonical redirects project to `__redirect-map.php`; `__redirect.php` consumes only that map and never opens MySQL. A deployment explicitly routes unresolved paths to this runtime or supplies equivalent adapter behavior.

Public discovery is also static. `site-index.json`, `sitemap.xml`, `sitemap.txt`, `llms.txt`, and optional `llms-full.txt` are regenerated from accepted public projections after clean routes are materialized. They may be deleted and rebuilt without loss of canonical state.

## Repository, hosting, and LLM workflow

See `docs/REPOSITORY-OPERATIONS.md` for Git/host sequencing and paired rollback. See `AGENTS.md` and `docs/LLM-COLLABORATION.md` before using an LLM to modify a site repository. Agents use the same repository/canonical/projection/deployment boundaries as human operators; conversation history and generated HTML never become authority.

## Migration policy

For any installed schema lower than the repository schema:

1. stop consequential writes;
2. take and restore-test a paired backup;
3. retain the older code with the older database until the declared migration is available;
4. inspect then apply only the migration matching the installed version;
5. verify the resulting schema before proceeding to the next migration;
6. run readiness, reconciliation when appropriate, and representative public projection checks before reopening writes.

Migrations fail outside their declared source range and advance `app_meta.schema_version` only after required structural changes succeed. Idempotent reruns are supported where the migration contract explicitly provides them.

## Rollback policy

Do not attempt to reverse accepted canonical writes table-by-table. For a failed upgrade:

1. stop CMS writes;
2. restore the verified pre-migration database backup;
3. restore the exact application revision/version paired with that backup;
4. rerun readiness against the restored pair;
5. verify representative public projections before reopening authoring.

For a failed fresh installation with no accepted content, discard the dedicated database and retry from a clean empty database after correcting configuration.

## Deployment boundary

FTP/SFTP, SSH/Git checkout, CI deployment, control-panel upload, containers, rewrite policy, and provider-specific repository updaters remain deployment adapters outside portable core. A successful rc.4 build or readiness report is evidence about the candidate; it is not authorization to migrate an installed site, add real provider credentials, deploy, or publish a GitHub release.
