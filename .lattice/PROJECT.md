# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.3` (schema v8, frozen artifact)
Current development baseline: `main` through merged M-019 (`53ebe3960e87a4f7732392a1bf2689569bc31412`)
Working branch: `release/0.1.0-rc.4`
Active milestone: M-020 — reusable discovery parity and `0.1.0-rc.4` release preparation
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-08-27 (America/New_York)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, private-data import, bulk messaging, destructive data actions, and any merge that directly triggers public release publication remain explicit operator/Principal boundaries.

## Frozen release truth

`0.1.0-rc.3` remains the published schema-v8 artifact. Later development does not redefine its tag, package, installation contract, release notes, or provenance. A later public release requires its own version/schema contract, exact-head verification, packaged-candidate rehearsal, proving-ground parity review, and explicit publication authorization.

## Integrated development truth

M-015 through M-019 are integrated to development `main`:

- M-015 / PR #20 — schema-v9 governed saved-block composition;
- M-016 / PR #21 — unified live Page Composer;
- M-017 / PR #22 — save edited page block as a new independent preset;
- M-018 / PR #23 — clean managed public routes;
- M-019 / PR #24 — schema-v10 Audience authority, governed double opt-in, authenticated Audience CMS, transactional mail adapters, and cPanel onboarding; merged as `53ebe3960e87a4f7732392a1bf2689569bc31412` after exact-head validate #377 and release-rehearsal #91 passed.

Those repository integrations do not imply installed-site migration, production deployment, provider credentials, live collection activation, or a new published release.

## Durable product truths

- MySQL owns accepted authored and other mutable canonical application state.
- Git owns code, rendering behavior, schema/migrations, tests, docs, adapters, and non-secret adopter configuration.
- Generated public files are replaceable outputs, not reverse source authority.
- Anonymous delivery should remain static-first/database-free where practical.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients never submit arbitrary structural HTML/CSS for governed composition.
- Secrets remain outside canonical SQL, public configuration, generated output, and browser-visible state.
- Database migrations are explicit CLI operations; bootstrap repair is not migration.
- Provider-specific transport and deployment integration remain adapter concerns and may not become application authority.
- Before a release line is cut, a fresh proving-ground comparison and clean packaged-candidate rehearsal are required.

## M-020 objective — reusable discovery parity + rc.4

Prepare the next public release candidate without weakening the frozen rc.3 contract.

### M-020A — deterministic LLM discovery parity

Proving-ground PR #68 established a reusable capability that belongs in the public core: deterministic `llms.txt` / optional `llms-full.txt` discovery derived from accepted public discovery state, normalized to clean reader routes, linked from public HTML with `rel="describedby"`, and regenerated at the final public projection boundary.

The public-core extraction must be site-neutral:

- generated discovery copy derives site identity/title/description from public configuration or discovery metadata rather than private proving-ground identity;
- public discovery projections never read private CMS state, subscriber records, credentials, drafts, or host-only markers;
- published HTML remains reader-facing truth; `site-index.json`, sitemap/feed files, `llms.txt`, and `llms-full.txt` remain derived projections;
- clean-route normalization happens after managed clean-route materialization;
- root `llms.txt` stays compact; an optional `llms-full.txt` may preserve expanded public context without becoming authority;
- generated HTML discovery metadata is idempotent and limited to public files.

Proving-ground PR #67 is classified differently: its exact private marker path and updater semantics are host-specific. Its reusable principle is preserved in deployment documentation/readiness design — missing operational provenance must not be represented as proof that canonical SQL is stale — but M-020 does not import proving-ground path names or private host state.

### M-020B — `0.1.0-rc.4` / schema v10 release line

After reusable parity is integrated on the same bounded milestone branch, promote the public candidate contract from rc.3/schema v8 to rc.4/schema v10.

Required release outcome:

- `VERSION`, release metadata, release notes, build/test contracts, and publisher identify `0.1.0-rc.4`, tag `v0.1.0-rc.4`, schema v10;
- fresh installations create the current schema-v10 authority directly;
- explicit supported upgrade evidence preserves the historical v7→v8→v9→v10 path and idempotence where the migration contract provides it;
- the public package includes current Composer, clean-route, Audience/mail, discovery, migrations, onboarding, installation, repository-operation, LLM-collaboration, and deployment-adapter surfaces without `.git`, `.github`, `.lattice`, tests, tools, secrets, adopter-local config, runtime/uploads, or proving-ground residue;
- the publisher is version-driven rather than hard-coded to rc.3 asset names or release-note paths;
- two exact-head builds are byte-identical; emitted and embedded manifests match and record the exact reviewed source revision;
- a clean packaged-candidate installation reaches schema v10 and exercises representative canonical mutation/projection behavior;
- upgrade rehearsal proves schema-v8 rc.3 → v9 → v10, including saved composition and Audience/mail authority;
- a fresh proving-ground parity check has no unresolved reusable-core delta at handoff.

## Verification contract

M-020 is technically satisfied only on the exact final PR head when:

1. cumulative `validate` is green, including public sanitization, all prior contracts/behavior suites, deterministic LLM-discovery structural/behavior coverage, schema-v10 release contract, PHP/JavaScript/Python syntax, and deterministic candidate build;
2. `release-rehearsal` is green from the packaged rc.4 candidate, including clean schema-v10 installation plus supported upgrade/migration rehearsals;
3. release metadata/tag/package naming is internally consistent and no rc.3 hard-coding can silently publish the wrong artifact;
4. PR review threads contain no unresolved technical finding; and
5. the final proving-ground comparison records no unresolved reusable-core delta.

A green candidate is a pre-publication handoff only.

## Explicit exclusions and consequence boundary

M-020 does not authorize production deployment, installed-site migration, real mail credentials, enabling live collection, private-list import, bulk/campaign messaging, destructive state mutation, or publication of `0.1.0-rc.4`.

Because the public-release workflow is triggered by release metadata reaching `main`, the release-preparation PR merge itself is treated as the Principal publication boundary. Director execution stops with a green, mergeable PR and exact-head evidence unless the Principal separately authorizes that merge/publication consequence.

## Next action

Implement M-020A and M-020B on `release/0.1.0-rc.4`, keep the PR synchronized as the remote milestone capsule, remediate required Quality/Assurance failures without weakening gates, and hand off only when the exact final head is green and publication is the sole remaining consequence.
