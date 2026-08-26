# AI Native CMS

AI Native CMS is a static-first publishing system designed for human and LLM-assisted iteration without turning generated files or conversation history into a second source of truth.

MySQL owns accepted authored state. Git owns application code, structural templates, migrations, tests, documentation, and adopter-owned public configuration. Authenticated humans and agents work through the same guarded contracts, while public HTML/JSON/XML and redirect routing data are deterministic projections. Anonymous readers do not require a database connection.

## Why “AI native”

An AI agent should be able to help redesign a site, revise content, add a feature, diagnose a bug, or prepare a release without bypassing CMS governance.

That means:

- repository-owned structure and code change through branches and reviewable diffs;
- accepted authored content/state remains canonical in MySQL;
- generated public output is evidence, never hidden reverse authority;
- schema changes require explicit migrations;
- human and agent writers share authentication, validation, revision, expected-hash, provenance, and projection rules;
- important decisions live in code, tests, documentation, and project state rather than only in a chat thread.

Start with `AGENTS.md` and `docs/LLM-COLLABORATION.md` when using a coding or content agent.

## Current candidate

The current internal candidate line is **`0.1.0-rc.3`**, schema version **8**.

rc.3 is licensed but remains private and unpublished. The selected terms are **Apache License 2.0 subject to the Commons Clause License Condition v1.0**. This is a **source-available** license, not an OSI-approved open-source license. It allows use, modification, derivative works, and commercial use while withholding the right to sell AI Native CMS itself, or a product/service whose value derives entirely or substantially from the CMS functionality. Attribution/license notices must be retained according to the terms.

The binding files are `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE`.

Repository visibility, a Git tag/GitHub Release, public package publication, and production deployment remain separate release decisions.

## Start with a real site

rc.3 ships a neutral starter site:

- `index.html` — Home;
- `about.html` — About;
- `writing.html` — Writing index;
- `assets/styles.css` — responsive starter design system;
- `assets/site.js` — database-free writing-index rendering;
- `templates/article.html` — long-form article shell.

Create adopter-owned **public, non-secret** configuration with:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
```

The initializer writes only `config/site.php`. It never asks for, reads, or writes database credentials or other secrets.

Then configure private runtime secrets outside the public root, bootstrap the database/first owner, and explicitly initialize repository content:

```bash
php database/bootstrap.php
php database/reconcile.php initial-import
```

Open `/cms/` over HTTPS. An unfinished site lands in the resumable **Onboarding** workspace, which derives progress from actual configuration, database, canonical-content, branding/navigation, publication, and readiness state. There is no separate wizard-complete flag.

See `docs/INSTALLATION.md` for the full first-run and upgrade path.

## CMS surface

The first-party `/cms/` workspaces cover:

- **Onboarding** — resumable first-run/site-build guidance derived from durable state;
- **Pages** — bounded editing of canonical text leaves;
- **Composer** — trusted-template page composition and new managed pages;
- **Media** — first-party asset catalog and bounded raster uploads;
- **Navigation** — canonical primary navigation;
- **Branding** — site identity plus adopter-declared bounded CSS tokens;
- **Writing** — Markdown long-form publishing with revisions and static projection;
- **Search + Social / SEO** — canonical page metadata plus read-only site-wide quality findings and deterministic social/schema projection;
- **Redirects** — governed same-site redirect authority and slug history;
- **Readiness** — authenticated read-only deployment/operability evidence.

There is no frontend framework or third-party active runtime dependency in the CMS UI.

## Authority model

### Git / repository authority

Repository files own application behavior, structural page shells/templates, CSS/JavaScript behavior, schema/migrations, tests, documentation, deployment adapters, and adopter-owned public non-secret configuration.

### Canonical MySQL authority

Accepted authored state includes page content leaves, typed page compositions, posts/revisions, media metadata, primary navigation, bounded branding values, SEO overrides, and redirect records.

### Generated public projection

HTML, JSON, XML, indexes, social/schema defaults, and redirect maps are deterministic outputs. Fix the owning repository/canonical source rather than editing the generated symptom and calling it durable.

### Host/provider state

Credentials, web-server rewrites, caching, document-root configuration, deployment mechanisms, and host-specific diagnostics remain deployment/operator concerns behind bounded adapters.

## Core capabilities

The reusable core includes:

- hardened PHP/MySQL owner authentication, HTTPS/origin/CSRF/session/rate-limit boundaries, and audit records;
- schema-v8 canonical content, revisions, provenance, composition, media, navigation, branding, SEO, and redirects;
- repository-owned page-source lineage with three-way reconciliation that preserves newer canonical edits;
- trusted structural templates exposing bounded rich-text, safe-link, and media values;
- canonical typed compositions and CMS-created root-level pages with validated parent hierarchy;
- bounded raster uploads and canonical media metadata;
- long-form Markdown publishing with immutable prior snapshots, stale-write rejection, restore, draft/published projection, and slug-history redirects;
- canonical SEO controls plus read-only site-wide duplicate/link/orphan/sitemap/canonical/H1/social/schema/alt quality checks;
- deterministic author/Open Graph/Twitter/fallback-schema enhancement that does not create a second SEO authority;
- guarded release-managed SEO compare-and-swap semantics that preserve newer canonical CMS authorship;
- redirect graph validation, global graph-write serialization, file/directory collision rejection, and database-free static redirect routing;
- deterministic site-wide finalization with bounded `after_pages`, `after_seo`, and final adapter hooks;
- CLI-only schema/first-owner bootstrap and explicit schema migrations;
- authenticated GET-only/CLI readiness with provider adapter seams;
- deterministic internal release-candidate packaging with exact source provenance and residue scanning.

## Repository → hosting operations

`docs/REPOSITORY-OPERATIONS.md` covers Git vs canonical SQL vs generated output vs host state, branch/PR workflow, SSH pull-to-host and reviewed-artifact/copy deployment patterns, secret placement, database backups/migrations, host-side hotfix recovery, readiness, rollback, and provider capability checks.

A Git deployment updates repository-owned behavior. It does **not** reset canonical CMS content from Git on every deploy.

## Working with an LLM

`AGENTS.md` is the repository-level contract an agent should read before changing the project. `docs/LLM-COLLABORATION.md` provides practical request patterns for interface/design iteration, content revision, features, bug fixes, schema/migration work, and release preparation.

The central rule is simple: an LLM can inspect, propose, implement, test, and explain changes, but it does not become a new authority layer.

For repository work, use a branch/PR and verification. For accepted content, use the canonical CMS/store contract. For generated-output problems, fix the owning source. For provider behavior, use an adapter. Do not hand an agent production secrets merely to make repository changes.

## Publishing and public delivery

`posts` is canonical. Published posts materialize static article HTML and a public JSON writing index; drafts have no public article projection. The starter writing page reads only that static index.

SEO overrides are canonical in `seo_overrides`; custom canonicals stay on the configured public origin. Site-wide SEO quality inspection is read-only, and deterministic social/schema enhancement runs through the final projection boundary. Primary navigation projects only into `<nav id="site-nav">`. Branding controls only identity text and CSS custom properties explicitly exposed by adopter configuration.

Manual redirects are canonical in `redirect_records`. Read-only system aliases come from repository configuration. Published slug changes enter redirect authority automatically. `__redirect.php` consumes a generated static map and never opens MySQL.

## Deployment adapters

`docs/DEPLOYMENT-ADAPTERS.md` defines the provider-neutral transport contract. Included Apache examples demonstrate serving existing files/directories first, routing unresolved requests to the database-free redirect runtime, denying direct map access, conservative public caching/compression, and private/preview `no-store` behavior.

They are examples, not automatic deployment behavior or CMS authority.

## Installation, migration, and readiness

Requirements:

- PHP 8.1+
- PDO MySQL (`pdo_mysql`)
- MySQL 5.7+ or MySQL 8.x using `utf8mb4`
- Python 3.10+ only for repository/release validation

Node is used only for repository-side JavaScript syntax validation.

An existing schema-7 installation must use the explicit migration after a verified backup/restore test:

```bash
php database/migrations/7-to-8.php --apply
```

`database/bootstrap.php --repair` is not a migration path.

Use the read-only readiness report after setup/deployment:

```bash
php database/readiness.php
```

## Release-candidate build and rehearsal

Build a deterministic internal candidate from an exact source revision:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits a ZIP, manifest, and SHA256 under `dist/`. It packages the license/NOTICE files, starter public site, onboarding, safe public site initializer, operator documentation, `AGENTS.md`, schema/migrations, SEO quality/projection support, redirect runtime, and deployment examples. It excludes repository governance/runtime state, tests/release tooling, adopter-local `config/site.php`, populated secret INI files, uploads/runtime state, and known private/adopter residue.

The final clean release gate is executable. `.github/workflows/release-rehearsal.yml` extracts the packaged candidate into an empty environment with a clean MySQL 8 service, then proves setup, schema/owner bootstrap, explicit reconciliation, authenticated onboarding, green readiness, governed repository and canonical-content changes, static redirect routing, paired filesystem/database restore, deterministic double-build identity, provenance, exclusions, and the `public:false` publication boundary.

M-011 through M-014 now have technical evidence. A fresh proving-ground comparison is still required at the final merge/publication gates; material reusable core changes reopen extraction automatically.

See `docs/RELEASE.md` for the full candidate and publication-gate contract.

## Publication boundary

After the final M-014 documentation head passes both the cumulative and clean-rehearsal gates and merges, delegated pre-publication engineering is complete.

The following remain separate Principal decisions:

1. repository visibility;
2. public tag/GitHub Release creation;
3. public package/download publication;
4. production deployment/adoption.

The selected license does not itself authorize those actions, and release metadata remains `public:false` until an explicit publication decision.

## Further documentation

- `docs/ARCHITECTURE.md` — system and authority model
- `docs/INSTALLATION.md` — fresh install, onboarding, migrations, backup, rollback
- `docs/REPOSITORY-OPERATIONS.md` — GitHub and hosting operations
- `docs/LLM-COLLABORATION.md` — governed agent-assisted iteration
- `docs/DEPLOYMENT-ADAPTERS.md` — host transport/interception contract
- `docs/UPSTREAMING.md` — moving reusable features between adopter/source repos
- `docs/EXTRACTION-MATRIX.md` — extraction/product frontier
- `docs/RELEASE.md` — internal candidate and publication gate
- `SECURITY.md` — security boundaries
