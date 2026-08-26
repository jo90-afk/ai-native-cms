# Extraction matrix

This is the bounded frontier for moving the production-proven CMS into a reusable product. It is a product map, not a backlog; Lattice derives executable work from the active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted | Structure only; no authored seed content |
| Repository page/document registry | `config/site.php` | adapter | extracted | Never hard-code adopter page or document names in core; keep repository source distinct from CMS-generated pages |
| Page block editing/revisions | `api/content-core.php`, `api/cms-pages.php` | core | extracted | Managed-page graph; sanitized bounded rich text; optimistic hashes; revision snapshot |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/content-sync.php` | core | extracted | Preserve canonical/source hashes, three-way reconciliation, immutable compare-and-swap update sets |
| Deterministic page/document projection | `api/content-authority.php`, `database/reconcile.php` | core | extracted | Reconcile -> update sets -> projection; anonymous reads remain static |
| Static rebuild/projector registry | `api/content-rebuild.php` | core + adapter hooks | extracted | Core owns phase order; trusted adopter scripts resolve inside repository root |
| CMS authentication + page editor UI | `api/cms-auth.php`, `cms/` | core | extracted | UI consumes guarded APIs; no second write model or third-party active runtime |
| Posts/drafts/revisions/publishing | `api/post-store.php`, `api/post-renderer.php`, `api/cms-writing.php`, `cms/writing.php` | core | extracted | MySQL canonical; optimistic hashes; bounded Markdown; static projection; adopter owns article template |
| SEO controls | `api/seo.php`, `api/cms-seo.php`, `cms/seo.php` | core | extracted | Canonical overrides reapply after projection; canonicals stay on configured public origin |
| Reusable block templates/composer | `api/composer.php`, `api/composition-store.php`, `api/cms-composer.php`, `cms/composer.php` | core | extracted | Structural HTML stays repository-owned; canonical composition stores template identities + typed values; generated pages consume but do not author templates |
| Media library | `api/media.php`, `api/cms-media.php`, `cms/media.php` | core | extracted | Canonical metadata + adopter-owned bytes; configured public roots; validated raster uploads only |
| New-page hierarchy | `api/composition-store.php`, Composer UI | core | extracted | CMS-created root-level routes use trusted shells/templates; validate parents and reject cycles; never feed generated routes back into repository lineage |
| Navigation | `api/navigation.php`, `api/cms-navigation.php`, `cms/navigation.php` | core | extracted | Ordered canonical SQL state; bounded safe destinations; optimistic hash; hierarchy-aware active projection into `#site-nav` only |
| Branding | `api/branding.php`, `api/cms-branding.php`, `cms/branding.php` | core + adapter token definitions | extracted | Core stores identity + adopter-declared bounded CSS custom properties, not one site’s CSS vocabulary |
| Portable database bootstrap | `database/bootstrap-core.php`, `database/bootstrap.php` | core | extracted | CLI-only schema + first owner; derive schema contract from `schema.sql`; no adopter content seed; no owner overwrite; no implicit migration |
| Production readiness | `api/readiness.php`, `api/cms-readiness.php`, `database/readiness.php`, `cms/readiness.php` | core + adapter checks | extracted | Read-only actionable report; no mutation or secret/grant-content disclosure; host-specific checks enter only through trusted repository-owned adapters |
| Browser credential-writing setup | deployment adapter | adapter | excluded from core | Core bootstrap does not write database credentials or expose a provider-specific setup surface |
| Internal release-candidate package | `VERSION`, `release/release.json`, `tools/build_release.py` | release engineering | implemented / verifying | Deterministic source ZIP + manifest + SHA256; exclude governance/runtime/adopter state; keep `public:false` and `licenseSelected:false` until explicit decisions |
| Installation/backup/rollback guidance | `docs/INSTALLATION.md`, `docs/RELEASE.md` | release engineering | implemented / verifying | Fresh install is schema/owner bootstrap -> explicit canonical import -> readiness; rollback pairs database and code state |
| Schema migrations/upgrades | future versioned migration layer | post-release compatibility | deferred until needed | This is the first candidate, so no prior public schema exists. Future schema changes must ship explicit source/target migrations; bootstrap must never substitute for them |
| Host repository updater | deployment adapter | adapter | later | Keep deployment authority explicit and host-specific |
| Newsletter/subscription | extension | optional extension | later | Generic capability; not required for CMS core readiness |
| Poetry-specific layout/visual projection | adopter extension | site/content-type adapter | excluded from core | Useful architecture may upstream; authored semantics remain adopter-owned |
| Project-record/Lattice public views | adopter extension | site integration | excluded from core | Lattice governs development; CMS must not require Lattice as public runtime |
| Personal portfolio content/theme | adopter repository | site-only | excluded | Never upstream authored content or identity |

## M-008 acceptance boundary

The first release-candidate artifact is a **private internal review artifact**, not a publication mechanism. Acceptance requires cumulative CI on the exact PR head, byte-for-byte reproducibility for a fixed source ref, inspected archive contents, correct reviewed-head provenance, and durable documentation of the installation and release boundaries.

Repository visibility, license selection, tag/release creation, package publication, production deployment, and production adoption remain Principal decisions. A green M-008 merge may prepare those choices; it may not make them.
