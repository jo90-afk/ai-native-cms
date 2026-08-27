# AI Native CMS — Lattice Project Capsule

Project ID: `ai-native-cms-001`
Product repository: this repository
Published release baseline: `0.1.0-rc.3` (schema v8, frozen artifact)
Current development baseline: `main` after merged M-015 → M-018
Working branch: `plan/audience-lists-cpanel-mail`
Active milestone: M-019 — Audience lists, collection, and mail-provider onboarding
Runtime contract: `lattice-app-works-platform-agnostic` 0.1.6
Principal alias: `Repository Owner`
Updated: **2026-08-27 (America/New_York)**

## Confirmed mandate

Maintain a site-neutral AI-native CMS with canonical SQL authority, repository-owned application behavior, deterministic/static public projection where practical, explicit migrations, bounded provider adapters, friendly onboarding, reversible repository operations, and governed human/LLM collaboration.

Routine reversible implementation, tests, documentation, parity refreshes, release-candidate preparation, and release assurance are delegated. Production deployment/adoption, credentials, public release publication, private-data import, bulk messaging, and destructive data actions remain explicit operator/Principal boundaries.

## Frozen release truth

`0.1.0-rc.3` remains the published schema-v8 artifact. Later merged development does not redefine its tag, package, installation contract, or provenance. A later public release requires its own release decision and rehearsal.

## Current development truth

M-015 through M-018 are integrated to development `main`.

- M-015 / PR #20 — schema-v9 governed saved-block composition;
- M-016 / PR #21 — unified live Page Composer;
- M-017 / PR #22 — save edited page block as a new independent preset;
- M-018 / PR #23 — clean managed public routes, merged as `d0e4edadc4ea367c2865b3f91355ffe22ee57ffb` after exact-head validate #351 and release-rehearsal #80 passed.

Those repository merges do not imply installed-site migration, production deployment, or a new published release.

## Durable product truths

- MySQL owns accepted authored and other mutable canonical application state.
- Git owns code, rendering behavior, schema/migrations, tests, docs, adapters, and non-secret adopter configuration.
- Generated public files are outputs, not reverse source authority.
- Human and agent writers converge on guarded mutation contracts.
- Browser clients never submit arbitrary structural HTML/CSS for governed composition.
- Secrets remain outside canonical SQL, public configuration, generated output, and browser-visible state.
- Database migrations are explicit CLI operations; bootstrap repair is not migration.
- Provider-specific transport is an adapter concern and may not become application authority.
- Before a future release line is cut, a fresh proving-ground comparison and clean rehearsal are required.

## M-019 — Audience lists, collection, and cPanel mail onboarding

**Implementation candidate on PR #24. Exact-final-head workflow evidence remains the technical acceptance surface; merge, installed-site migration, deployment, credentials, and release publication remain separate boundaries.**

The proving ground’s subscriber/admin behavior is treated as evidence, not copy authority. M-019 generalizes the reusable mechanism around an explicit public-core Audience authority.

### Canonical authority and migration

M-019 advances development schema v9 → v10 with explicit CLI migration.

- `audience_lists` owns stable list identity, operator/public labels, purpose/confirmation copy, and active/disabled state.
- `audience_subscriptions` owns normalized list membership, pending/confirmed/unsubscribed state, consent timestamps, resend state, hashed confirmation tokens, and bounded source provenance.
- the latent legacy `subscribers` primitive is copied into the generic authority and renamed to `subscribers_legacy_archive`; imported lists start disabled so historical membership is preserved without silently activating a new public collection surface.
- `mail_outbox` remains a development/log transport rather than campaign or subscriber authority.

No parallel audience store is introduced.

### CMS and public collection

The candidate provides an authenticated **Audience** workspace with list configuration, membership counts/inspection, pending resend, operator unsubscribe, bounded CSV export, transactional-mail status, and explicit test send.

Each Audience list creates or refreshes a server-generated governed Signup preset in the **Audience** block category. Page Composer can place that preset without browser-submitted structural form HTML.

The generic public collection endpoint:

1. accepts same-site, rate-limited POST signup requests;
2. normalizes list/email input and uses a honeypot plus non-enumerating responses;
3. stores only SHA-256 of cryptographically random confirmation bearer tokens;
4. enforces a 15-minute resend cooldown and 30-day pending expiry;
5. lets confirmation-link GET render a noindex review screen but never create consent;
6. confirms only through explicit POST;
7. preserves unsubscribed rows as suppression state until a new explicit signup starts another double-opt-in cycle.

### Mail/provider boundary

`api/mail-transport.php` provides bounded transactional adapters:

- `smtp` with certificate/peer verification, implicit TLS or STARTTLS, and authenticated delivery;
- deliberate local PHP `mail` for hosts that choose it;
- `log` for development/CI unless an explicit private override is configured.

The CMS never stores SMTP credentials in canonical SQL or public config and never returns the password through browser-visible status. The cPanel guide is `docs/CPANEL-EMAIL.md`: use **Email Accounts → Connect Devices → Secure SSL/TLS Settings (Recommended)**, copy the exact provider hostname/port/security values into private `AINCMS_MAIL_*` configuration, send one explicit CMS test message, and inspect **Email Deliverability** for SPF/DKIM/DMARC issues.

Browser onboarding derives only safe mail readiness state and never writes or echoes provider passwords.

### Verification contract

PR #24 must pass on its exact final head:

- the full cumulative `validate` workflow, including public sanitization, all existing contracts/behavior suites, Audience/mail structural checks, pure behavior checks, PHP/JavaScript/Python syntax, and deterministic rc.3 construction;
- the full `release-rehearsal` workflow, preserving the frozen rc.3 empty-site path, the schema-v9 composition upgrade path, and the new schema-v10 Audience migration/legacy-preservation/double-opt-in/fake-SMTP path;
- PR review-thread inspection with no unresolved technical findings.

Earlier passing candidate runs are evidence during remediation but do not substitute for exact-final-head assurance.

### Explicit exclusions

M-019 does not authorize campaign composition, bulk sends, CRM/segmentation, tracking pixels, automatic private-list imports, production provider credentials, installed-site migration, deployment, or public release publication.

## Next action

Complete exact-final-head Quality/Assurance on PR #24. If green, move the PR to the Principal pre-merge handoff. A repository merge would authorize integration only; installed-site schema migration, enabling real collection, provider credentials, production deployment, and public release publication remain explicit operator/Principal actions.
