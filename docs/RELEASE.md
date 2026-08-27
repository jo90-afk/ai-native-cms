# Release process

AI Native CMS `0.1.0-rc.4` is the current **public release candidate**, schema version 10. Its required Git tag is `v0.1.0-rc.4`. The published `0.1.0-rc.3` tag remains a frozen schema-v8 predecessor and upgrade fixture; rc.4 does not redefine it.

The release is source-available under **Apache License 2.0 subject to Commons Clause License Condition v1.0**. It is not OSI-approved open source. The binding files are `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE`.

## Release identity

`VERSION` defines the candidate version. `release/release.json` defines product/channel/schema/runtime metadata, license state, public-distribution authorization, package root, and required version tag.

For rc.4:

- version: `0.1.0-rc.4`;
- channel: `public-release-candidate`;
- schema: 10;
- public distribution: authorized;
- required tag: `v0.1.0-rc.4`;
- license: Apache 2.0 + Commons Clause v1.0.

`tools/build_release.py` refuses disagreement between `VERSION`, `database/schema.sql`, release metadata, channel, tag, or license state.

## Deterministic build

Build from an exact Git revision:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

For rc.4 the builder emits:

- `dist/ai-native-cms-0.1.0-rc.4.zip`
- `dist/ai-native-cms-0.1.0-rc.4.manifest.json`
- `dist/ai-native-cms-0.1.0-rc.4.sha256`

Sorted paths, fixed ZIP metadata, deterministic compression, and exact source-ref provenance make repeated builds byte-for-byte comparable.

## Public package contents

The package contains the reusable CMS/runtime; neutral Home/About/Writing starter; Page Composer and Block Composer; clean-route projection; Audience list/double-opt-in surfaces; transactional mail adapters and cPanel onboarding; deterministic public discovery (`site-index.json`, sitemaps, `llms.txt` projection code); schema-10 bootstrap plus explicit historical migrations; reconciliation/readiness/onboarding; SEO/redirect behavior; deployment-adapter examples; repository/LLM collaboration documentation; and license/release metadata.

It excludes `.git/`, `.github/`, `.lattice/`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, populated INI files, symlinks, credentials, and known proving-ground/adopter residue.

The package preserves `database/migrations/7-to-8.php`, `8-to-9.php`, and `9-to-10.php` so existing installations can advance sequentially while fresh installs bootstrap directly at schema 10.

## Manifest and checksum

The embedded/external `RELEASE-MANIFEST.json` records product/version/channel, exact source revision, schema version, runtime requirements, public/license/tag distribution flags, package root, and per-file byte length/SHA256. The outer `.sha256` hashes the complete ZIP.

## Required verification

Before creating the rc.4 GitHub prerelease:

1. cumulative CI is green on the exact reviewed head;
2. the clean packaged rc.4 rehearsal is green on that same head;
3. two candidate builds are byte-identical;
4. ZIP SHA256 matches the emitted checksum;
5. embedded and external manifests are identical and record the exact source revision;
6. fresh installation reaches schema 10 and current canonical tables without retired active authorities;
7. the published rc.3/schema-8 upgrade fixture successfully traverses 8 → 9 → 10, including composition and Audience/mail behavior;
8. current Composer, clean-route, Audience/mail, discovery, onboarding, repository-operation, LLM-collaboration, and deployment-adapter surfaces are packaged;
9. `LICENSE`, `LICENSE-APACHE-2.0.txt`, and `NOTICE` are packaged;
10. no adopter-local configuration, personal residue, private repository identifiers, secret material, subscriber data, or host-only operational state enters the package;
11. repository-visible governance files are reviewed for public suitability;
12. a fresh proving-ground parity check shows no unresolved reusable-core delta.

A green release candidate is evidence and a pre-publication handoff, not publication authorization.

## Proving-ground parity

The proving ground is an implementation source and validation environment, not public-core copy authority. Material reusable mechanisms found there reopen extraction before a new release candidate is accepted. Site-authored content, identity, and host-specific operational details do not.

For M-020, deterministic LLM discovery from the proving ground was generalized into site-neutral core projection. A separate deployment-provenance repair was classified as host-specific at the path/marker level; its reusable principle remains that missing operational provenance must not be falsely represented as canonical SQL staleness.

## GitHub publication

`.github/workflows/publish-release.yml` is version-driven. On an authorized publication merge to `main` it:

1. resolves `VERSION`, `release/release.json`, and `release/RELEASE-NOTES-<version>.md`;
2. validates the public and release-candidate contracts;
3. builds artifacts from the exact `main` SHA;
4. attempts public repository visibility with the workflow token;
5. creates or verifies the exact metadata tag without repointing an existing tag;
6. creates the prerelease or refreshes assets for that same tag/SHA;
7. verifies the version-derived ZIP, manifest, and SHA256 asset names.

The normal workflow token may not have repository-administration permission. If visibility cannot be changed, release/tag creation may still succeed while repository visibility remains an owner action.

Because release metadata on `main` triggers this publisher, the rc.4 release-preparation PR merge is a **Principal publication consequence boundary**. Technical readiness does not authorize that merge.

## Installation and upgrades

Fresh rc.4 installation:

```bash
php setup/site.php --name="My Site" --url=https://example.com --owner="Site Owner"
php database/bootstrap.php
php database/reconcile.php initial-import
php database/readiness.php
```

Fresh bootstrap is schema 10.

Published rc.3 is schema 8 and upgrades explicitly:

```bash
php database/migrations/8-to-9.php --apply
php database/migrations/9-to-10.php --apply
php database/readiness.php
php database/reconcile.php post-migration
```

Schema 7 first applies `database/migrations/7-to-8.php --apply`. `database/bootstrap.php --repair` is never a migration path. Back up and prove restore before migration.

See `docs/INSTALLATION.md` for the detailed sequence, `docs/REPOSITORY-OPERATIONS.md` for repository-to-host operation, `docs/CPANEL-EMAIL.md` for mail-provider setup, and `docs/LLM-COLLABORATION.md` for governed iterative work.

## Prerelease status

`0.1.0-rc.4` remains a prerelease. It is intended for evaluation, site builds, and governed production trials with tested backups and explicit review of migrations, deployment, and provider configuration before consequential actions.

Future release candidates repeat the proving-ground parity and packaged-candidate gates. Material reusable core changes reopen extraction; site-only or governance-only changes do not.
