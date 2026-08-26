# Release process

AI Native CMS `0.1.0-rc.3` is the first **public release candidate**, schema version 8. Its required Git tag is `v0.1.0-rc.3`.

The release is source-available under **Apache License 2.0 subject to Commons Clause License Condition v1.0**. It is not OSI-approved open source. Use, modification, derivative works, attribution-preserving redistribution, and commercial use are permitted subject to the Commons Clause restriction on selling the CMS itself or a product/service whose value derives entirely or substantially from the CMS functionality.

The binding files are `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE`.

## Release identity

`VERSION` defines the release-candidate version. `release/release.json` defines product/channel/schema/runtime metadata, license state, public-distribution authorization, and the required version tag.

For rc.3 the contract is:

- version: `0.1.0-rc.3`;
- channel: `public-release-candidate`;
- schema: 8;
- public distribution: authorized;
- tag required: `v0.1.0-rc.3`;
- license selected: Apache 2.0 + Commons Clause v1.0.

`tools/build_release.py` refuses a mismatch between `VERSION`, schema, channel, public-distribution state, tag, or license metadata.

## Deterministic build

Build from an exact Git revision:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits:

- `dist/ai-native-cms-0.1.0-rc.3.zip`
- `dist/ai-native-cms-0.1.0-rc.3.manifest.json`
- `dist/ai-native-cms-0.1.0-rc.3.sha256`

Sorted file order, fixed ZIP metadata, deterministic compression, and exact source-ref provenance make repeated builds byte-for-byte comparable.

## Public package contents

The package contains the reusable CMS/runtime, starter Home/About/Writing site, onboarding, public site initializer, canonical schema/bootstrap/migration/reconciliation/readiness tools, SEO audit/projection support, redirect runtime, deployment-adapter examples, operator documentation, `AGENTS.md`, license/NOTICE files, and release metadata.

It excludes `.git/`, `.github/`, `.lattice/`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, populated INI files, symlinks, credentials, and known proving-ground/adopter residue.

Schema-v8 packages include the explicit `database/migrations/7-to-8.php` migration and the generated-map redirect runtime (`__redirect.php` / `__redirect-map.php`). Reference deployment adapters remain examples, not automatic deployment or CMS authority. See `docs/DEPLOYMENT-ADAPTERS.md`.

## Manifest and checksum

The embedded/external `RELEASE-MANIFEST.json` records product/version/channel, exact source revision, schema version, runtime requirements, public/license/tag distribution flags, package root, and per-file byte length/SHA256. The outer `.sha256` hashes the complete ZIP.

## Required verification

Before creating or replacing the GitHub prerelease:

1. cumulative CI is green on the exact reviewed head;
2. the clean packaged-candidate rehearsal is green on the exact reviewed head;
3. two candidate builds are byte-identical;
4. ZIP SHA256 matches the emitted checksum;
5. embedded and external manifests are identical and record the exact source revision;
6. schema is 8 and the explicit migration + redirect runtime are packaged;
7. `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` are packaged;
8. starter/onboarding, repository operations, LLM collaboration, and deployment adapter docs are packaged;
9. no adopter-local configuration, personal residue, private repository identifiers, or secret material exists in the package;
10. repository-visible governance files are reviewed for public suitability;
11. a fresh production proving-ground parity check shows no unresolved reusable core delta.

M-014 completed the clean release rehearsal from the packaged rc.3 candidate with a fresh MySQL 8 database, authenticated onboarding, canonical content/redirect mutation, deterministic projection, governed agent/repository change, and paired filesystem/database restore.

## GitHub publication

`.github/workflows/publish-release.yml` is the authorized rc.3 publisher. On the publication merge to `main` it:

1. resolves and validates the public release metadata;
2. runs the public/release-candidate contracts;
3. builds artifacts from the exact `main` SHA;
4. attempts to set repository visibility to public using the workflow token;
5. creates or verifies tag `v0.1.0-rc.3`;
6. creates the GitHub prerelease or refreshes its assets;
7. verifies the ZIP, manifest, and SHA256 are the release assets.

GitHub's normal workflow token may not have repository-administration permission. If the visibility step cannot change a private repository, the tag and prerelease can still be created privately; an owner must then change repository visibility to **Public** in GitHub settings. The release becomes visible with the repository.

The publisher is idempotent for the same tag/SHA and refuses to silently repoint an existing release tag to a different commit.

## Installation and upgrades

Fresh install path:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
php database/bootstrap.php
php database/reconcile.php initial-import
php database/readiness.php
```

Then sign in at `/cms/` and use the state-derived Onboarding workspace.

An existing schema-7 installation must back up and test restore, then use:

```bash
php database/migrations/7-to-8.php --apply
```

`database/bootstrap.php --repair` is not a migration path.

See `docs/INSTALLATION.md` for onboarding, backup, migration, readiness, and paired rollback; `docs/REPOSITORY-OPERATIONS.md` for GitHub-to-host operation; and `docs/LLM-COLLABORATION.md` for governed iterative work with an LLM.

## Prerelease status

`0.1.0-rc.3` is intentionally a prerelease. It is ready for public evaluation and site builds, but adopters should keep tested backups and review migrations/deployment changes before production use.

Future release candidates must repeat the production proving-ground parity gate. Material reusable core changes reopen extraction; site-only or governance-only changes do not.
