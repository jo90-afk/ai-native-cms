# Extraction matrix

This is the bounded frontier for moving the production-proven CMS into the public product. It is a product map, not a backlog; Lattice derives executable work from the active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted | Structure only; no authored seed content |
| Editable-page/document registry | `config/site.php` | adapter | extracted | Never hard-code adopter page or document names in core |
| Page block editing/revisions | `api/content-core.php`, `api/cms-pages.php` | core | extracted | Configured page registry; sanitized bounded rich text; optimistic hashes; revision snapshot |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/content-sync.php` | core | extracted | Preserve canonical/source hashes, three-way reconciliation, immutable compare-and-swap update sets |
| Deterministic page/document projection | `api/content-authority.php`, `database/reconcile.php` | core | extracted | Reconcile -> update sets -> projection; anonymous reads remain static |
| Static rebuild/projector registry | `api/content-rebuild.php` | core + adapter hooks | extracted | Core owns phase order; trusted adopter scripts resolve inside repository root |
| CMS authentication + page editor UI | `api/cms-auth.php`, `cms/` | core | extracted | UI consumes guarded APIs; no second write model or third-party active runtime |
| Posts/drafts/revisions/publishing | `api/cms-writing.php`, `cms/writing.php` and helpers | core | next | Remove site categories/content defaults; retain Markdown/static projection |
| SEO controls | `api/cms-seo.php`, `cms/seo.php` | core | next | Origin restrictions use configured public origin |
| Reusable block templates/composer | `api/cms-composer.php`, CMS composer UI | core | planned | Structural HTML remains server-owned; values are typed/bounded |
| Media library | `api/cms-media.php`, `cms/media.php` | core | planned | First-party asset catalog; adopter controls storage root/limits |
| Navigation | `api/cms-navigation.php`, CMS navigation UI | core | planned | Site-wide state canonical in SQL; projection hook is generic |
| Branding | `api/cms-branding.php`, CMS branding UI | core | planned | Product stores bounded design tokens, not one site’s CSS vocabulary |
| New-page hierarchy | composition APIs/UI | core | planned | Preserve parent validation, cycle rejection, discovery projection hooks |
| Production readiness | `api/cms-readiness.php` | core + adapter checks | planned | Separate portable checks from host-specific checks |
| Shared-host bootstrap/setup | `database/bootstrap.php`, `setup/` | deployment adapter | planned | Generic defaults; no hosting-vendor identity in core |
| Host repository updater | deployment adapter | adapter | later | Keep deployment authority explicit and host-specific |
| Newsletter/subscription | extension | optional extension | later | Generic capability; not required for CMS core readiness |
| Poetry-specific layout/visual projection | adopter extension | site/content-type adapter | excluded from core | Useful architecture may upstream; authored semantics remain adopter-owned |
| Project-record/Lattice public views | adopter extension | site integration | excluded from core | Lattice governs development; CMS must not require Lattice as public runtime |
| Personal portfolio content/theme | adopter repository | site-only | excluded | Never upstream authored content or identity |

## Immediate frontier after M-003

Extract the reusable writing/publishing path and SEO controls onto the same canonical/projection runtime. Long-form content is the next major authored object type needed for a generally useful publishing product; search/social metadata should travel with that publishing boundary rather than becoming a later competing layer.
