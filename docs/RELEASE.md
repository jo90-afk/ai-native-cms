# Release-candidate process

AI Native CMS `0.1.0-rc.1` is an **internal release candidate**, not a public release.

A release candidate is a reproducible review artifact. It does not make the repository public, select or imply a license, create a Git tag or GitHub Release, publish a package, authorize deployment, or adopt the core into any production site.

## Candidate identity

The candidate identity is split across two version-controlled files:

- `VERSION` — the candidate semantic version;
- `release/release.json` — product/channel/schema/runtime metadata and explicit distribution-boundary flags.

`tools/build_release.py` refuses to build when those files disagree, when the declared schema version differs from `database/schema.sql`, or when the metadata says public distribution or license selection has already occurred.

## Build

From a clean repository checkout:

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The default output directory is `dist/`, which is local generated state and must not be committed.

The builder produces:

- `dist/ai-native-cms-0.1.0-rc.1.zip`
- `dist/ai-native-cms-0.1.0-rc.1.manifest.json`
- `dist/ai-native-cms-0.1.0-rc.1.sha256`

The ZIP has sorted file order, fixed entry timestamps and permissions, and deterministic compression settings. Building the same source tree with the same `--source-ref` must produce the same bytes and SHA256 digest.

## Candidate contents

The source candidate includes product/runtime code and reusable documentation:

- `README.md`, `SECURITY.md`, `VERSION`;
- `release/release.json`;
- `api/`;
- `cms/`;
- generic `config/` files, excluding adopter-local `config/site.php`;
- portable `database/` schema/bootstrap/reconcile/readiness/example configuration;
- `docs/`.

It intentionally excludes development/governance/runtime state:

- `.git/`, `.github/`, `.lattice/`;
- `tests/` and `tools/`;
- `dist/`;
- runtime/upload directories;
- adopter-local `config/site.php`;
- populated INI files and other deployment credentials.

The builder fails closed on symlinks in the candidate set, known reference-adopter identifiers, obvious private-key/token patterns, or non-example INI files.

## Manifest

`RELEASE-MANIFEST.json` is embedded in the ZIP and also emitted beside it. It records:

- product/version/channel;
- exact source revision supplied to the builder;
- schema version;
- minimum runtime versions;
- distribution-boundary flags;
- package root;
- every packaged file’s byte length and SHA256 digest.

The embedded manifest is not self-hashed in its own file list. The outer `.sha256` file hashes the complete ZIP.

## Verification

Repository CI must pass the cumulative M-001–M-008 validation suite before a candidate can be called reviewable. M-008 adds `tests/release_candidate_contract.py`, which builds the candidate twice from the same fixed source ref and requires byte-for-byte equality, then opens the ZIP and verifies required files and exclusions.

For a human review candidate, verify at minimum:

1. the cumulative CI run is green on the exact source head;
2. the generated ZIP SHA256 matches the emitted `.sha256` file;
3. `RELEASE-MANIFEST.json` reports the intended source revision and schema version;
4. no adopter/local configuration is present;
5. `docs/INSTALLATION.md` accurately describes fresh install, backup, migration boundaries, readiness, and rollback;
6. `SECURITY.md` still describes the disclosure and secret-handling posture;
7. the candidate contains no `LICENSE` file unless the Principal has explicitly selected one.

## License and public-distribution boundary

No license is selected by this milestone. Absence of a license means this internal candidate should not be treated as an authorized public/open-source distribution.

Selecting a license is a Principal decision. Once selected, the release process must add the correct license text and update `release/release.json` deliberately; the current builder is expected to refuse the old `licenseSelected: false` candidate contract once a future public-release milestone changes that boundary.

Similarly, repository visibility, Git tags, GitHub Releases, package registries, public downloads, and production deployment remain separate explicit actions.

## Tagging and publication

M-008 does not create a tag. A future publication workflow should require all of the following to be explicit and mutually consistent before tagging:

- final version;
- selected license;
- exact source commit;
- release notes/changelog;
- schema/migration support statement;
- candidate SHA256;
- public visibility/distribution authorization.

Until those exist, `0.1.0-rc.1` remains an internal review identity only.
