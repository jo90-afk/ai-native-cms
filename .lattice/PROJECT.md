# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Baseline branch: `main`
Baseline commit: `eea0c0f9a6faf0b147551dc797e4b39a43d3038a`
Working branch: `release/m014-clean-rehearsal`
Runtime: `lattice-app-works-platform-agnostic` 0.1.6 contract
Principal alias: `Repository Owner`
Updated: **2026-08-26 (America/New_York)**

## Confirmed mandate

Maintain a public-releasable, site-neutral AI-native CMS extracted from a production proving ground. Preserve canonical SQL authority, repository/template ownership, static public delivery, revisions/provenance, explicit migrations, deterministic projection, and bounded host adapters. Make the product approachable enough that a new adopter can create, operate, deploy, and iteratively evolve a site with human or LLM assistance without bypassing the authority model.

Routine reversible implementation, tests, documentation, repository-side merges, proving-ground parity refreshes, internal release-candidate preparation, and pre-release rehearsal are delegated. Repository visibility, public/tagged release creation, package publication, production deployment/adoption, credentials, and destructive data actions remain Principal boundaries.

The selected license is **Apache License 2.0 subject to Commons Clause License Condition v1.0**. Describe it as **source-available**, not OSI-approved open source.

## Durable product truths

- Anonymous public delivery is static-first and database-free.
- MySQL owns accepted authored state. Git owns code, structural templates/shells, CSS/JS behavior, schema/migrations, tests, docs, adapters, and public non-secret adopter configuration.
- Repository source may propose canonical content but never silently overwrite newer accepted database state.
- Generated HTML/JSON/XML/indexes/redirect maps are outputs, not reverse source authority.
- Human and agent writers converge on the same guarded mutation contracts.
- Structural HTML remains repository/template-owned; CMS state stores trusted template identities and bounded typed values.
- Repository-authored pages and CMS-created pages remain distinct source classes.
- SEO quality inspection is observational. Canonical page-specific SEO remains in `seo_overrides`; deterministic social/schema enhancement is projection only.
- Release-managed SEO updates may seed missing state but may replace an existing override only by exact expected-predecessor hash.
- Database bootstrap owns schema structure and the first persisted owner only; migrations remain explicit.
- Browser onboarding is authenticated and state-derived but never writes credentials, migrates, deploys, or creates a second onboarding authority.
- Redirect authority is canonical in SQL; anonymous routing consumes only generated static state.
- Readiness is observational and may not mutate, migrate, deploy, publish, or expose secrets.
- LLM collaboration preserves branches/review, canonical-state boundaries, migrations, tests, and secret separation.
- Internal candidate generation is not publication. Candidate metadata remains `public:false` until a separate Principal decision.
- Before publication, a fresh proving-ground comparison is mandatory. Only material reusable core deltas reopen extraction.

## Satisfied milestones

- **M-001 — Generic executable foundation:** runtime/database/security seams, adopter config, schema, CI, sanitization.
- **M-002 — Canonical page/document authority:** optimistic editing, revisions, three-way reconciliation, compare-and-swap update sets.
- **M-003 — Operable page CMS/projector hooks:** first-party Pages workspace and bounded deterministic hooks.
- **M-004 — Long-form publishing and SEO:** canonical posts/revisions, bounded Markdown, static projection, same-origin SEO.
- **M-005 — Typed composition and media:** repository-owned templates, typed compositions, bounded media.
- **M-006 — Page hierarchy/navigation/branding:** trusted-shell page creation, parent validation, canonical navigation and bounded branding.
- **M-007 — Portable bootstrap/readiness:** CLI schema/owner bootstrap, explicit content import, read-only readiness/adapters.
- **M-008 — Reproducible packaging:** deterministic ZIP/manifest/SHA256, provenance, residue guards, installation/rollback docs.
- **M-009 — Schema-v8 redirects/finalization:** explicit 7→8 migration, canonical redirects, DB-free anonymous routing, unified final projection.
- **M-010 — Reference deployment adapters:** provider-neutral transport contract and Apache examples.
- **M-011 — Friendly onboarding/starter:** merged through PR #15 at `ab7039efa34a8b00f7357c23f18fb5a9d1251135`.
- **M-012 — Repository/hosting operations:** merged through PR #15.
- **M-013 — Governed LLM collaboration:** merged through PR #15.
- **OBJ-SEO-PARITY — Site-wide SEO quality parity:** reusable audit/projection/CAS mechanics merged through PR #16 at `eea0c0f9a6faf0b147551dc797e4b39a43d3038a`.

## M-014 — Clean empty-site release rehearsal

**Technically satisfied on rehearsal implementation head `43a981e4d995294c48f4022fc724bb2c48392da4`, subject to the final documentation-head gates and PR merge.**

Executable evidence:

- push rehearsal run `33006245237` passed against a clean MySQL 8 service;
- cumulative PR validation run **#189** (`33006335119`) passed on the same exact head;
- PR-triggered clean rehearsal run `33006335107` also passed on the same exact head;
- the candidate was built twice and ZIP/manifest/checksum were byte-identical;
- candidate version `0.1.0-rc.3`, schema 8, 96 packaged files, exact source revision recorded;
- candidate ZIP SHA256: `bf5aca65a8339db1bc153dad120ebc5caa9ccf9bc05825eb7a6bc9b611ab3c9b`;
- release metadata remained `public:false` with selected license metadata present;
- clean packaged candidate created public non-secret site config through `setup/site.php`;
- real MySQL schema/owner bootstrap reached schema 8/8, followed by explicit repository reconciliation;
- readiness reached **18 pass / 2 nonblocking warnings / 0 blocking failures**;
- authenticated HTTP login succeeded and state-derived onboarding reported all 5 required steps complete, all six starter files present, and overall ready state;
- representative agent repository work used a separate Git branch, changed repository-owned structure plus a small JavaScript feature, passed syntax/structural checks, and remained a bounded reviewable diff;
- representative canonical content changed through `contentAuthorityCommitBlock` with expected-hash protection and deterministic page projection;
- representative canonical redirect projected a static DB-free routing map and exercised the anonymous router entry point;
- paired filesystem tar + MySQL dump restore removed the rehearsal mutations and returned readiness to **18 pass / 2 warnings / 0 blockers**;
- final candidate residue/provenance checks passed.

The two warnings were expected rehearsal-environment observations: development runtime mode and a bootstrap password hash still configured after owner persistence. Neither was blocking.

## Fresh proving-ground parity after M-014

The proving-ground head advanced during the rehearsal to `538376c7ebade27fe8cabcc884efa4d88369ec3c`. The intervening PR changed only `.lattice/PROJECT.md` and `.lattice/evidence/delivery-2026-08-25.md`. It is governance/evidence-only and contains no reusable CMS-core delta, so parity remains closed.

A final proving-ground head check is still required immediately before M-014 merge. Any newer material reusable core change reopens extraction.

## Active frontier — Principal publication gate

After the final documentation-head cumulative validation + clean rehearsal both pass and PR #17 is merged, delegated pre-publication work is complete.

The remaining actions are Principal decisions, separately authorized:

1. repository visibility;
2. public release tag / GitHub Release;
3. public package/download publication;
4. production deployment/adoption.

License selection is already resolved, but it does not itself authorize any of those actions.
