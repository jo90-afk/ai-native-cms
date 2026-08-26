# Extraction matrix

This is the bounded frontier for moving the production-proven CMS into the public product. It is a product map, not a backlog; Lattice derives executable work from the active milestone conditions.

| Capability | Public destination | Classification | Status | Extraction rule |
| --- | --- | --- | --- | --- |
| MySQL transport and secret loading | `api/database.php` | core | extracted | Generic `AINCMS_*` configuration; secrets outside public root |
| HTTPS/origin/session/auth/CSRF/rate limits/audit | `api/runtime.php` | core | extracted | Preserve fail-closed production behavior; no adopter identity |
| Canonical schema | `database/schema.sql` | core | extracted | Structure only; no authored seed content |
| Editable-page registry | `config/site.php` | adapter | extracted seam | Never hard-code adopter page names in core |
| Page block editing/revisions | `api/cms-lib.php`, `api/cms-pages.php`, `cms/pages.php` | core | next | Replace hard-coded allowlist with site config |
| Canonical content commit/reconciliation | `api/content-authority.php`, `api/cms-content-authority.php` | core | next | Preserve canonical/source hashes and compare-and-swap semantics |
| Full static rebuild | `api/content-rebuild.php`, `database/rebuild-public.php` | core + adapter hooks | next | Core orchestrates; adopter registers projectors/outputs |
| Posts/drafts/revisions/publishing | `api/cms-writing.php`, `cms/writing.php` and helpers | core | planned | Remove site categories/content defaults; retain Markdown/static projection |
| SEO controls | `api/cms-seo.php`, `cms/seo.php` | core | planned | Origin restrictions use configured public origin |
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

## Immediate frontier after M-001

The next high-leverage slice is canonical page editing plus content reconciliation. It joins the already-extracted security/database foundation to the product’s defining authority model and creates the write contract that later human and agent interfaces can share.
