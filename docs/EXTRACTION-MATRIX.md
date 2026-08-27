# Extraction and release matrix

This file distinguishes the frozen published `0.1.0-rc.3` artifact from integrated development and the current `0.1.0-rc.4` release-preparation milestone.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport + private secret loading | `api/database.php` | core | released | Generic `AINCMS_*`; secrets stay outside public root |
| Auth/origin/CSRF/session/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Published predecessor schema | rc.3 `database/schema.sql` | compatibility truth | frozen — schema 8 | Tag/artifact remain unchanged and are the rc.4 upgrade fixture |
| Current fresh-install schema | `database/schema.sql` | core | rc.4 candidate — schema 10 | Fresh rc.4 installs current authority directly |
| Explicit upgrades | `database/migrations/` | compatibility core | rc.4 candidate — 7→8→9→10 | Bootstrap repair/browser requests are never migration |
| Canonical reconciliation | content authority/sync + CLI | core | released/evolved | Three-way source/canonical reconciliation; browser never silently promotes repository source |
| Reusable block presets | schema-9+ `block_presets` | core | integrated M-015 | Browser never submits arbitrary structural HTML/CSS |
| Page Composer + source adoption | composition store/CMS | core UX/authority | integrated M-016 | Typed/server-owned structure; stale first adoption fails closed |
| Save edited block as new preset | Page Composer | core UX | integrated M-017 | New independent recipe without mutating source/page instance |
| Clean managed routes | page route/projection core | projection core | integrated M-018 | Stable internal keys project to reader-facing `/slug/` routes |
| Canonical redirects + static router | redirect APIs + `__redirect*` | core | released | Graph safety in SQL; anonymous routing database-free |
| Audience lists/subscriptions | schema-10 tables | core canonical state | integrated M-019; rc.4 candidate | Generic SQL authority; migrated legacy lists start disabled |
| Audience CMS + CSV | `/cms/audience.php` + APIs | core UX | integrated M-019; rc.4 candidate | Bounded operations; no token hashes/internal IDs/bulk sending |
| Governed Signup block | generated Audience preset | composition UX | integrated M-019; rc.4 candidate | Server owns form/list structure |
| Public double opt-in | `/api/audience-subscribe.php` | public core | integrated M-019; rc.4 candidate | Scanner-safe GET review + explicit POST confirmation |
| Transactional mail | `api/mail-transport.php` | provider adapter | integrated M-019; rc.4 candidate | SMTP/mail/log replaceable; credentials remain private |
| cPanel mail onboarding | docs/private-config/onboarding | provider docs/UX | integrated M-019; rc.4 candidate | Exact Connect Devices settings + explicit test + deliverability review |
| Deterministic public discovery | `api/discovery-projection.php` | projection core | M-020 candidate | Derived only from indexable same-site public HTML after clean routes |
| Compact LLM discovery | `api/llms-projection.php` | projection core | M-020 candidate | `llms.txt` routes to public state; optional expanded context; private state excluded |
| Public discovery relation | projected public HTML | projection metadata | M-020 candidate | Idempotent `rel="describedby"` to `/llms.txt` |
| Deterministic release package | release metadata + builder | release engineering | rc.4 candidate | Exact source provenance, reproducible ZIP, manifest/SHA256 |
| Version-driven GitHub publisher | publish workflow | release engineering | M-020 candidate | Exact version/tag/notes/assets; existing tag never silently repointed |
| License and attribution | license files | distribution governance | unchanged | Apache 2.0 + Commons Clause v1.0; source-available |
| Site identity/content/private host mechanics | adopter/proving-ground repo | site-only/adapter | excluded | Reusable mechanism may upstream; identity/private operations do not |

## Frozen predecessor

`0.1.0-rc.3` remains the published schema-v8 release. Its tag, package, notes, schema, and provenance are historical truth. Rc.4 uses the actual `v0.1.0-rc.3:database/schema.sql` file in release rehearsal to prove the supported upgrade rather than reconstructing v8 from current source.

## Integrated post-release milestones

- **M-015 / PR #20** — schema-v9 governed presets, Block Composer, typed composition.
- **M-016 / PR #21** — unified live Page Composer, source adoption/convergence, retired separate Pages mutation concept.
- **M-017 / PR #22** — save edited instance as a new independent preset.
- **M-018 / PR #23** — clean managed public routes and conditional legacy-route adapter behavior.
- **M-019 / PR #24** — schema-v10 Audience authority, double opt-in, CMS operations/export, transactional mail boundary, cPanel onboarding; merged to development `main` as `53ebe3960e87a4f7732392a1bf2689569bc31412` after exact-head validate #377 and release-rehearsal #91.

None of those repository integrations by themselves authorize installed-site migration, provider credentials, public activation, deployment, or a new public release.

## M-020 — reusable discovery parity + rc.4

PR #25 is the bounded release-preparation surface.

### Reusable proving-ground extraction

The last material proving-ground reusable delta was merged PR #68: deterministic LLM-oriented discovery. The public-core implementation deliberately generalizes the mechanism instead of copying site identity/taxonomy:

- discovery starts from indexable same-site **public HTML** and canonical URLs;
- legacy and clean-route files collapse onto the same public canonical;
- `site-index.json`, `sitemap.xml`, and `sitemap.txt` are deterministic projections;
- compact `llms.txt` is generated from that public index;
- an optional existing `llms-full.txt` may retain an expanded public-context body below the synchronized compact index;
- public HTML receives one idempotent `rel="describedby"` relation;
- CMS state, subscriber data, credentials, drafts, private APIs, and host-only operational markers are excluded.

Proving-ground PR #67 is classified as host-specific implementation at the marker/path layer. Its reusable rule is documented: failure to observe deployment provenance is an unknown state, not evidence that canonical SQL is stale.

### Fresh parity result

A fresh proving-ground commit sweep on 2026-08-27 found **no commits after merged PR #68 (`e1b1d5eb99edd3da4f909ada19def9ab6b7bc12d`)**. Therefore M-020 has no unresolved later proving-ground reusable-core delta at this handoff stage.

### Rc.4 release contract

Rc.4 is `0.1.0-rc.4`, schema 10, tag `v0.1.0-rc.4`.

Fresh installation bootstraps schema 10. Upgrade rehearsal resets the database to the exact published rc.3 schema-v8 tag, then proves 8→9 composition conversion and 9→10 Audience/legacy-preservation/double-opt-in/mail behavior. The package includes all historical migrations 7→8→9→10.

The publisher resolves version/tag/release-note/asset names dynamically. The release-preparation PR merge is intentionally the Principal publication boundary because release metadata reaching `main` triggers the publisher.

## Assurance boundary

M-020 is ready only when the **exact final PR #25 head** has:

1. cumulative `validate` green, including public sanitization, discovery, Audience, schema evolution, behavior/syntax, and reproducible candidate build;
2. `release-rehearsal` green, including clean schema-10 package install plus the actual published rc.3 upgrade chain;
3. no unresolved review thread;
4. no unresolved proving-ground reusable-core delta.

Technical satisfaction authorizes only the pre-publication candidate. Merge/publication, installed-site migrations, production credentials, public collection activation, and deployment remain explicit Principal/operator consequences.
