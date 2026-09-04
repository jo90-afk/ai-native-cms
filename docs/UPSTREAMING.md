# Upstreaming from adopter sites

AI Native CMS is expected to have real adopter sites that sometimes discover or build a useful capability before the public core does. The repository is structured so those changes can return upstream without importing the adopter’s content or identity.

## The rule

If a behavior is useful to more than one site, put the behavior in a path-compatible core file and put the site’s values behind configuration or an adapter.

Do not solve a reusable problem by adding another site-name conditional to core code.

## Recommended adopter layout

Keep the upstream-compatible CMS paths intact:

```text
api/
cms/
database/
tests/
config/
```

An adopter may keep additional site code anywhere else. `config/site.php` and site-owned adapters are the intended seams for labels, page registries, theme behavior, content-type registration, and projection destinations.

A site that wants stricter Git separation may vendor this repository as a subtree under a stable prefix, but the files inside that prefix should retain the same relative paths.

## Feature workflow

1. Classify the change before implementation.
   - **core**: authentication, content authority, revision/provenance, publishing, composition, navigation, branding, media, SEO, projection, generic operations;
   - **adapter**: hosting provider, site framework, custom content type, site-specific projector;
   - **site-only**: copy, theme, private integration, one site’s taxonomy.
2. For core work, avoid adopter identifiers in the implementation and add configuration/hooks instead.
3. Add a generic regression test in the same relative `tests/` area as the implementation.
4. Run the adopter’s complete regression suite plus the public-release contract.
5. Export/cherry-pick the generic commits into AI Native CMS.
6. Run public CI without the adopter’s private data or secrets.
7. Only after the upstream change is accepted should an adopter collapse temporary compatibility shims.

## Compatibility contract

Core changes are upstream-ready when all of these are true:

- no personal names, domains, repository names, credentials, or private paths are required by core code;
- environment variables use the `AINCMS_` product prefix;
- editable pages and site labels come from `config/site.php` or a registered adapter;
- database migrations contain no adopter content seeds;
- public projection remains deterministic from canonical state plus explicit site configuration;
- source reconciliation does not overwrite a newer canonical value merely because Git ran later;
- human and agent writes use the same authorization, validation, revision, audit, and projection contracts;
- regression coverage can run with the example fixture site.

## Source parity

Path compatibility is preferred over a generated abstraction layer. A maintainer comparing an adopter’s `api/runtime.php` with the public `api/runtime.php` should see product changes directly and site differences concentrated at explicit seams.

When a future refactor intentionally breaks path compatibility, treat that as a versioned migration and provide an adapter/migration guide. Do not allow gradual topology drift to become the integration strategy.

The public Markdown discovery increment is a generic forward adaptation: its source set comes from the public discovery index and current public HTML, with no fixed portfolio routes or adopter titles. Its three runtime paths are `api/discovery-projection.php`, `api/markdown-projection.php`, and `api/llms-projection.php`. Adopt them as one reviewed unit and run the discovery contract plus cumulative gates. Preserve any site-specific index schema or full-corpus production behind an adapter; the generic index uses `site` and `pages`. No schema migration or release tag rewrite is required for this increment.

## What never moves upstream

Do not upstream:

- authored site content or database dumps;
- private evidence or personal project state;
- credentials, host account names, private email addresses, or deployment secrets;
- personal analytics identifiers;
- theme assets unless intentionally contributed as an example/template;
- claims or records whose meaning depends on a private repository.
