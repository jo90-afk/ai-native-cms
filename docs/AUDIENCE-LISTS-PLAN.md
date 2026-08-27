# M-019 — Audience lists and collection

Status: **implementation in progress on PR #24**. Canonical Audience authority, public double-opt-in collection, CMS management/export, generated Signup blocks, and the transactional mail transport are now implemented on the milestone branch. Provider onboarding and exact-head assurance remain before pre-merge handoff.

## Goal

Add a first-class **Audience** area to AI Native CMS so an adopter can create named email lists, place a governed signup form on a public page, collect addresses with double opt-in, inspect/export list membership in the authenticated CMS, and send confirmation mail through a bounded provider transport.

The first documented provider path is a cPanel-hosted mailbox using authenticated SMTP. cPanel is a transport adapter, not subscriber authority.

## Authority model

M-019 advances development schema v9 → v10 with an explicit CLI migration.

Canonical SQL:

- `audience_lists` owns stable list identity, operator/public labels, purpose copy, confirmation copy, and active/disabled state.
- `audience_subscriptions` owns normalized membership state: pending, confirmed, or unsubscribed; consent timestamps; resend state; token hashes; and bounded provenance.
- the frozen schema-v8 `subscribers` primitive is migrated when present, copied into Audience authority, and renamed to `subscribers_legacy_archive` only after successful import. Imported lists start disabled so legacy membership is preserved without silently publishing a new public signup surface.
- `mail_outbox` remains a development/log transport only, not campaign authority.

Git owns schema/migrations, Audience API/store logic, CMS interface, generated Signup-block structure, provider transport adapters, onboarding/readiness behavior, tests, and documentation.

## Implemented collection contract

`/api/audience-subscribe.php` is the generic same-site collection boundary.

- signup mutation is POST-only;
- same-origin enforcement and the existing rate-limit store apply;
- list key + email are bounded/normalized;
- honeypot requests return the same generic success result;
- new, pending, existing-confirmed, and scanner-fetch cases do not reveal membership state;
- resend cooldown is 15 minutes;
- pending confirmation expires after 30 days;
- raw bearer tokens are never stored; SQL keeps SHA-256 only;
- GET confirmation links render a noindex confirmation screen but do not confirm;
- confirmation requires an explicit POST from that screen;
- unsubscribed rows remain suppression/consent history but an explicit new signup may start a new pending confirmation.

## Generated Signup block

Each saved Audience list creates or refreshes one governed `block_presets` entry named `Signup — <list label>` in the **Audience** category. The server generates the complete form structure and fixed list key. Browser clients never submit structural form HTML.

The block can be placed through Page Composer like another governed preset. Public form submission targets the generic Audience endpoint; membership state stays in SQL.

## CMS surface

`/cms/audience.php` provides:

- create/edit list configuration;
- active/disabled list state;
- total/pending/confirmed/unsubscribed counts;
- bounded member inspection;
- pending confirmation resend with cooldown;
- operator unsubscribe preserving suppression state;
- confirmed CSV export with no internal IDs/token hashes;
- outgoing mail configuration status with secret values omitted;
- explicit test-send action;
- direct handoff to Page Composer and cPanel provider documentation.

No campaign composer, bulk send, CRM, tracking pixel, enrichment, or marketing automation is included.

## Mail transport

`api/mail-transport.php` implements replaceable transactional transport:

- `smtp` — authenticated SMTP with peer/certificate verification, implicit TLS (`ssl`) or STARTTLS (`tls`), and no unencrypted production fallback;
- `mail` — deliberate host-local PHP mail adapter;
- `log` — development/CI outbox only unless an explicit private override is set.

Private `AINCMS_MAIL_*` configuration is the only credential source. Status exposed to CMS/browser reports transport/host/port/security/from and whether a username is present; the password is never returned.

## Remaining milestone work before handoff

1. wire the private-config example and onboarding state to the implemented transport;
2. add structural/behavior contracts and a schema-v10 MySQL rehearsal after the existing v9 rehearsal;
3. run exact-head validate + release rehearsal;
4. self-review the full PR for sanitization, consent-state preservation, and secret leakage;
5. update Lattice capsule/PR evidence and move the PR out of draft only when all gates pass.

Production migration/deployment, public release publication, and bulk outbound mail remain Principal/operator boundaries.
