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

**Planning frontier. Implementation has not started.**

The proving ground’s subscriber/admin work demonstrated useful behavior but depended on a site-specific `subscribers` authority and one fixed list. The Principal has now authorized a generic public-core mandate rather than a one-off port.

### Goal

Add an authenticated **Audience** CMS area where adopters can define lists and inspect/export membership, plus a governed page-block/public endpoint for double-opt-in collection. Confirmation mail uses a generic private transport boundary; cPanel authenticated SMTP is the first documented provider path.

### Planned authority

M-019 advances development schema v9 → v10 with explicit migration and introduces canonical list/subscription state rather than a parallel site-specific table.

Planned canonical objects:

- `audience_lists` — stable list key, operator/public labels, bounded confirmation copy, active/disabled state, audit ownership;
- `audience_subscriptions` — list-scoped normalized email, pending/confirmed/unsubscribed state, consent timestamps, one-time hashed confirmation token, resend timestamp and bounded provenance;
- development-only mail outbox/log transport where needed for tests.

Mail credentials remain private runtime configuration and never enter those tables.

### Planned user flow

1. Create an Audience list in the CMS.
2. Add a governed Signup form block and select an active canonical list.
3. Public visitor submits an email; response does not reveal prior membership state.
4. CMS stores/renews pending membership and sends confirmation through the configured mail adapter.
5. Confirmation-link GET displays a noindex confirmation screen; only explicit POST confirms, preventing link scanners from creating consent.
6. Operator sees All / Pending / Confirmed / Unsubscribed membership and may export a scoped CSV without token hashes/internal IDs.

No campaign composer or bulk-send capability is authorized in M-019.

### Mail/provider boundary

Planned transports:

- `log` for development/CI;
- authenticated `smtp` for production provider integration;
- optional deliberate local PHP `mail` adapter, never the only production path.

The cPanel guide is `docs/CPANEL-EMAIL.md`. Onboarding will tell operators to create/choose a mailbox, use cPanel **Connect Devices** to obtain the exact Secure SSL/TLS outgoing settings, place credentials in private `AINCMS_MAIL_*` configuration, recheck readiness, send one explicit test email, and review cPanel **Email Deliverability** for SPF/DKIM/DMARC issues.

Browser onboarding remains state-derived and may not save or echo SMTP passwords.

### Implementation plan

Detailed design and acceptance contract: `docs/AUDIENCE-LISTS-PLAN.md`.

Preferred tranches:

- M-019A — schema v10 + list/subscription authority + Audience admin;
- M-019B — public double-opt-in collection + governed Signup form primitive;
- M-019C — SMTP/mail adapter + cPanel documentation + onboarding/readiness + explicit test-send.

Use stacked PRs if necessary to keep migration, public consent flow, and transport independently reviewable.

### Security/consent invariants

- same-origin/rate-limit/honeypot controls on public collection;
- generic non-enumerating responses;
- confirmation tokens are random, stored only as hashes, expire after 30 days, and resend no more often than every 15 minutes;
- GET does not confirm subscription;
- disabled lists refuse new collection without deleting historical consent state;
- provider secrets are redacted from diagnostics and logs;
- list exports are authenticated/audited and exclude secrets/internal IDs;
- unsubscribed state is preserved as suppression/consent history rather than silently deleted;
- no private proving-ground subscribers/list names are imported automatically.

## Next action

Review the M-019 planning PR. If accepted, implementation begins with M-019A and the schema-v9→v10 migration boundary. Production migration, provider credentials, enabling public collection, private-list imports, and bulk email remain outside technical milestone acceptance.
