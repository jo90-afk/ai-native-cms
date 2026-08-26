# Repository and hosting operations

This guide explains how to manage an AI Native CMS site as a GitHub repository and move verified changes to a hosting provider without blurring repository, database, generated-output, and host authority.

The examples are provider-neutral. Apache reference adapters are included, but Apache is not required by the CMS core.

## What belongs where

### Commit to Git

Keep these in the repository:

- application code under `api/` and `cms/`;
- structural page shells and reusable templates;
- CSS, JavaScript, and public static assets intended to ship with the site;
- adopter-owned `config/site.php` **only if it contains no secrets**;
- schema and explicit migrations;
- tests and documentation;
- deployment adapters or provider configuration examples that contain no credentials.

### Keep in canonical MySQL state

The CMS database owns accepted page content, typed composition values, posts/revisions, media metadata, navigation, branding values, SEO overrides, redirects, and other canonical authored records.

Do not treat a Git checkout as a complete database backup, and do not overwrite newer canonical state just because repository source is older.

### Generated public output

Generated HTML/JSON/XML/indexes/redirect maps are projections. They may live in the deployed document root, but they are not the reverse source of truth for code or canonical content.

### Keep outside the public repository

Never commit:

- database passwords;
- private keys;
- hosting control-panel credentials;
- private API tokens;
- real secret INI files;
- runtime upload/temp/session state that is not intentionally public content.

Use documented environment variables, host secret stores, or a private configuration file outside the public root.

## Start a site repository

A typical first repository flow is:

```bash
git clone <cms-source-or-template> my-site
cd my-site
cp config/site.example.php config/site.php
```

Edit `config/site.php` for public structure only: site identity, configured repository pages, writing route/template, media roots, navigation defaults, branding tokens, redirects, readiness adapters, and projection hooks. Do not put credentials in it.

Create your own remote repository and push the baseline according to your Git hosting workflow.

A future onboarding initializer may automate part of this starter structure; the authority split remains the same.

## Branch and pull-request workflow

For repository-owned changes:

1. update local `main`;
2. create a focused branch;
3. make the change;
4. run targeted tests;
5. push the branch;
6. open a PR;
7. let cumulative CI validate the exact head at milestone/release checkpoints;
8. review authority/security/migration impact;
9. merge only verified work;
10. deploy the merged revision separately.

Example:

```bash
git checkout main
git pull --ff-only
git checkout -b feat/site-header-refresh
# edit / test
git add -A
git commit -m "Refresh site header"
git push -u origin feat/site-header-refresh
```

Do not make production deployment the automatic side effect of opening a PR unless you intentionally configure a deployment system with its own protected environment and rollback controls.

## First host deployment

Before copying code, identify these host facts:

- public document root;
- PHP version (>= the candidate requirement);
- MySQL version (>= the candidate requirement);
- database name/user/host and where secrets will live;
- HTTPS/public origin;
- rewrite/front-controller capability if using static redirects;
- writable locations for approved uploads/runtime output;
- backup destination and schedule;
- deployment method;
- shell/SSH availability, if any.

Then:

1. deploy the repository/candidate files;
2. create secret configuration outside the public root or set the documented environment variables;
3. run `php database/bootstrap.php ...` for a fresh database and first owner;
4. run the explicit repository reconciliation/import step for initial canonical source;
5. log into the CMS and complete onboarding/site build-out;
6. run `php database/readiness.php` and resolve blocking failures;
7. apply the appropriate deployment adapter behavior for unresolved redirects/caching;
8. verify public pages and private CMS no-store behavior.

Bootstrap does not write credentials for you and does not silently migrate older schemas.

## Deployment pattern A — SSH pull-to-host

Use this when the host has Git and shell access and the repository can be fetched securely.

Recommended shape:

```bash
ssh <host>
cd <site-root>
git fetch origin
git checkout main
git pull --ff-only origin main
php database/readiness.php
```

For a schema-changing release:

1. back up the database;
2. deploy code to a staging/release directory or otherwise preserve the previous code revision;
3. run the explicitly documented migration;
4. run rebuild/readiness;
5. switch traffic or finish the pull only when verification passes.

Do not embed a reusable personal SSH private key in the repository. Use host-level deploy keys or other provider-supported credentials.

## Deployment pattern B — Build/copy artifact

Use this when the host should not have repository credentials or Git access.

1. choose an exact reviewed source revision;
2. run the deterministic candidate/site build in a trusted environment;
3. verify checksum and manifest;
4. copy the approved artifact to the host using SFTP/SCP/provider file deployment or a CI protected environment;
5. preserve secret files and runtime/upload data outside the replaced artifact tree;
6. run any explicit migration and readiness checks;
7. switch/reload only after verification.

The packaged CMS release intentionally excludes `.git`, CI/governance state, tools/tests, adopter-local secrets, and runtime state.

## Canonical content and Git deployment

A Git deployment updates repository-owned behavior. It does **not** mean database content should be reset from Git on every deploy.

Repository content reconciliation follows the canonical/source lineage rules. If canonical SQL has diverged from the previous repository source, newer canonical content is preserved rather than silently overwritten.

When you intentionally want a repository-authored change to supersede canonical content, use the explicit reconciliation/update-set mechanism rather than deleting or recreating the database.

## Host-side hotfixes

Avoid editing production files directly. If an emergency host edit is unavoidable:

1. record exactly what changed;
2. immediately recreate the fix on a branch in Git;
3. test and merge the repository version;
4. redeploy the verified revision;
5. confirm the host matches the durable source.

If the host edit was wrong or temporary, discard it instead. Do not allow a live working tree to become an untracked competing source of truth.

## Database backups and migrations

Before a migration or destructive maintenance operation:

- create a database backup and verify that it is readable;
- record the exact code/source revision paired with that database state;
- review the migration's source version, target version, preflight, and rollback instructions;
- keep the prior deployable code revision available.

AI Native CMS does not use bootstrap repair as a version-upgrade mechanism. Each public schema transition must have an explicit migration.

## Rebuild and readiness

After repository changes that affect public projection, run the normal deterministic rebuild path rather than editing generated files manually.

Readiness is observational. Use it after deployment to confirm configuration, database/schema state, owner state, canonical source initialization, output paths, and adapter checks. A readiness report must not mutate the site.

## Rollback

A safe rollback pairs code and database state.

For a code-only release:

- restore/check out the previous verified code revision;
- rebuild deterministic public projection if necessary;
- rerun readiness.

For a schema-changing release:

- follow the migration-specific rollback instructions;
- restore the database backup if the migration is not reversibly defined;
- restore the code revision compatible with that database schema;
- rebuild and verify readiness before reopening traffic.

Do not roll back code across an incompatible schema boundary and hope the application will adapt.

## Provider checklist

Before declaring a provider supported, answer:

- What is the document root?
- Can secrets live outside it?
- Which PHP/MySQL versions are available?
- How are URL rewrites/fallback routing configured?
- How are static cache/compression rules configured?
- Can private CMS responses retain `no-store`?
- How are writable upload paths isolated from application source?
- Is shell/SSH available?
- If Git is available, how are deploy credentials scoped?
- If Git is unavailable, what artifact-copy mechanism is available?
- How are database backups created and restored?
- How is a previous code release restored?
- Are scheduled jobs available if future extensions need them?

Provider-specific answers belong in an adapter or deployment note, not in CMS canonical state.

## Working with an LLM on the repository

Tell the agent whether you are asking for a repository-owned change, canonical content change, generated-output diagnosis, or host-adapter change. For repository work, require a branch/PR and verification. For canonical content, require the CMS/store contract. Never give an agent production credentials merely to make repository changes.

See `AGENTS.md` and `docs/LLM-COLLABORATION.md` for the full collaboration contract.
