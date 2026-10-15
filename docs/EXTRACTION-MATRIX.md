# Extraction and release matrix

This file distinguishes frozen published predecessors, integrated reusable development, and the current `0.1.0-rc.5` release-preparation milestone.

| Capability | Public destination | Classification | Status | Product rule |
| --- | --- | --- | --- | --- |
| MySQL transport + private secret loading | `api/database.php` | core | released | Generic `AINCMS_*`; secrets stay outside public root |
| Auth/origin/CSRF/session/rate limits/audit | `api/runtime.php` | core | released | Fail closed in production |
| Published schema-8 upgrade fixture | rc.3 `database/schema.sql` | compatibility truth | frozen | Tag/artifact remain unchanged and are the 8→9→10 fixture |
| Published schema-10 predecessor | rc.4 artifact/tag | release truth | frozen | Immediate predecessor to rc.5; no schema migration needed to rc.5 |
| Current fresh-install schema | `database/schema.sql` | core | rc.5 candidate — schema 10 | Fresh installs create current authority directly |
| Explicit upgrades | `database/migrations/` | compatibility core | rc.5 candidate — 7→8→9→10 | Bootstrap repair/browser requests are never migration |
| Canonical reconciliation | content authority/sync + CLI | core | released/evolved | Three-way source/canonical reconciliation; browser never silently promotes repository source |
| Reusable block presets | schema-9+ `block_presets` | core | released | Browser never submits arbitrary structural HTML/CSS |
| Page Composer + source adoption | composition store/CMS | core UX/authority | released | Typed/server-owned structure; stale first adoption fails closed |
| Save edited block as new preset | Page Composer | core UX | released | New independent recipe without mutating source/page instance |
| Clean managed routes | page route/projection core | projection core | released | Stable internal keys project to reader-facing `/slug/` routes |
| Canonical redirects + static router | redirect APIs + `__redirect*` | core | released/evolved | Graph safety in SQL; anonymous routing database-free; CMS writes finalize through shared publication boundary |
| Audience lists/subscriptions | schema-10 tables | core canonical state | released | Generic SQL authority; migrated legacy lists start disabled |
| Audience CMS + CSV | `/cms/audience.php` + APIs | core UX | released | Bounded operations; no token hashes/internal IDs/bulk sending |
| Governed Signup block | generated Audience preset | composition UX | released | Server owns form/list structure |
| Public double opt-in | `/api/audience-subscribe.php` | public core | released | Scanner-safe GET review + explicit POST confirmation |
| Transactional mail | `api/mail-transport.php` | provider adapter | released | SMTP/mail/log replaceable; credentials remain private |
| Deterministic public index/sitemaps | `api/discovery-projection.php` | projection core | released | Derived only from indexable same-site public HTML after clean routes |
| Public Markdown alternates | `api/markdown-projection.php` | projection core | rc.5 candidate / M-021 | Verified adjacent `.md` output; authored collisions and unsafe paths fail closed |
| Compact LLM discovery | `api/llms-projection.php` | projection core | rc.5 candidate / M-021 | `llms.txt` prefers verified Markdown; optional expanded context; private state excluded |
| Publication locking/outcomes | `api/publication-lock.php` | core runtime | rc.5 candidate / M-022 | Save truth and publication truth are separate; pending recovery blocks follow-on writers |
| Publication adapter contract | `docs/PUBLICATION-OUTCOMES.md` | core/adapter seam | rc.5 candidate / M-022 | Host generations/selectors stay adapter-specific |
| Deterministic release package | release metadata + builder | release engineering | rc.5 candidate | Exact source provenance, reproducible ZIP, manifest/SHA256 |
| Version-driven GitHub publisher | publish workflow | release engineering | released/evolved | Exact version/tag/notes/assets; existing tag never silently repointed |
| License and attribution | license files | distribution governance | unchanged | Apache 2.0 + Commons Clause v1.0; source-available |
| Site identity/content/private host mechanics | adopter/proving-ground repo | site-only/adapter | excluded | Reusable mechanism may upstream; identity/private operations do not |

## Frozen predecessors

`0.1.0-rc.3` remains the published schema-v8 upgrade origin. Its tag, package, notes, schema, and provenance are historical truth. Release rehearsal uses the actual `v0.1.0-rc.3:database/schema.sql` fixture to prove the supported migration chain.

`0.1.0-rc.4` remains the immediate schema-10 predecessor. Rc.5 does not redefine or repoint its tag/artifacts. Because rc.5 remains schema 10, existing rc.4 installations do not need a database migration solely for the version change.

## Accepted post-rc.4 development

- **M-021 / PR #26** — adjacent public Markdown alternates, strict public-source validation, and compact LLM routing that prefers verified Markdown.
- **M-022 / PR #27** — shared private publication locking, canonical-save versus publication outcome semantics, bounded `cms-publication` pending recovery, follow-on writer blocking, and redirect convergence on complete finalization.

These mechanisms are the material reusable-core delta published by rc.5.

## Rc.5 release contract

Rc.5 is `0.1.0-rc.5`, schema 10, tag `v0.1.0-rc.5`.

Fresh installation bootstraps schema 10. Upgrade rehearsal resets the database to the exact published rc.3 schema-v8 tag, then proves 8→9 composition conversion and 9→10 Audience/legacy-preservation/double-opt-in/mail behavior. The package includes all historical migrations 7→8→9→10.

The publisher resolves version/tag/release-note/asset names dynamically. The authorized release-preparation PR merge is the public publication boundary because release metadata reaching `main` triggers the publisher.

## Proving-ground parity

The private production proving ground is an implementation source, not copy authority. The parity sweep immediately preceding rc.5 identified publication correctness as the material reusable delta and upstreamed it in M-022. Site identity, authored corpus, private host paths, immutable serving-generation implementation, selector policy, and shared-host executable discovery remain adopter/deployment-adapter state.

No unresolved reusable-core delta is intentionally carried into rc.5.

## Assurance boundary

Rc.5 is publishable only when the **exact final release head** has:

1. cumulative `validate` green, including public sanitization, schema evolution, publication outcomes, discovery, Audience/mail, behavior/syntax, and reproducible candidate build;
2. `release-rehearsal` green, including clean schema-10 package install plus the actual published rc.3 upgrade chain;
3. the dedicated publication-outcome contract/behavior green;
4. no unresolved review thread;
5. no unresolved proving-ground reusable-core delta.

The Principal explicitly authorized this rc.5 publication. Installed-site adoption, production credentials, private-data migration, and adopter deployment remain separate consequences.
