# Release-candidate process

AI Native CMS `0.1.0-rc.2` is an **internal release candidate**, not a public release. It supersedes rc.1 for current source-parity evaluation because rc.2 carries schema v8 redirect authority and projection-boundary hardening. `0.1.0-rc.1` remains historical evidence for the earlier schema-7 extraction.

A candidate is a reproducible review artifact. It does not make the repository public, select or imply a license, create a Git tag or GitHub Release, publish a package, authorize deployment, or adopt the core into production.

## Candidate identity

`VERSION` defines the semantic candidate version. `release/release.json` defines product/channel/schema/runtime metadata and explicit distribution-boundary flags. `tools/build_release.py` refuses mismatched version/schema metadata or a candidate whose metadata has crossed the current private/unlicensed boundary.

## Build

```bash
python3 tools/build_release.py --source-ref <git-sha>
```

The builder emits deterministic files under `dist/`:

- `ai-native-cms-0.1.0-rc.2.zip`
- `ai-native-cms-0.1.0-rc.2.manifest.json`
- `ai-native-cms-0.1.0-rc.2.sha256`

Sorted file order, fixed ZIP metadata, deterministic compression, and exact source-ref provenance make repeated builds byte-for-byte comparable.

## Candidate contents

The source candidate includes reusable runtime/product code, `README.md`, `SECURITY.md`, release metadata, `api/`, `cms/`, generic config examples, schema/bootstrap/migrations/reconciliation/readiness, root redirect runtime/map seed, portable `docs/`, and optional deployment adapter examples under `adapters/`.

It excludes `.git/`, `.github/`, `.lattice/`, tests, release tooling, generated `dist/`, runtime/upload state, adopter-local `config/site.php`, populated INI files, symlinks, credentials, and known reference-adopter residue.

Schema-v8 candidates must include `api/redirects.php`, `api/cms-redirects.php`, `cms/redirects.php`, `database/migrations/7-to-8.php`, `__redirect.php`, and `__redirect-map.php`.

M-010 adds reference deployment adapters, not automatic deployment. The candidate includes `adapters/apache/public.htaccess.example`, `adapters/apache/private.htaccess.example`, and `docs/DEPLOYMENT-ADAPTERS.md` so an adopter can implement unresolved-path redirect interception and conservative caching/compression without making Apache a core runtime assumption.

## Manifest

The embedded/external `RELEASE-MANIFEST.json` records product/version/channel, exact reviewed source revision, schema version, runtime requirements, distribution flags, package root, and per-file byte length/SHA256. The outer `.sha256` hashes the complete ZIP.

## Verification

CI must pass the cumulative M-001–M-010 gate on the exact candidate head. For the review artifact verify at minimum:

1. CI is green on the exact reviewed head;
2. ZIP SHA256 matches the emitted checksum;
3. embedded and external manifests are identical and record that head;
4. schema version is 8 and the explicit 7→8 migration plus redirect runtime are packaged;
5. Apache reference deployment adapters are packaged but no host-specific credentials/provider assumptions are embedded;
6. no adopter/local configuration, private residue, or `LICENSE*` entry exists;
7. `docs/INSTALLATION.md` accurately covers fresh install, explicit 7→8 migration, readiness, source reconciliation, backup, and paired rollback;
8. `docs/DEPLOYMENT-ADAPTERS.md` accurately separates portable CMS authority from host interception/cache/compression behavior;
9. `SECURITY.md` still describes secret/request/static-routing boundaries.

The CI artifact remains short-lived and private. It is evidence for review, not publication.

## License and public-distribution boundary

No license is selected. `release/release.json` remains `public:false` and `licenseSelected:false`, and the release contract rejects a `LICENSE*` entry in this state.

Selecting a license, changing repository visibility, creating a tag/GitHub Release, publishing a package/download, or deploying/adopting the core are separate Principal decisions.

## Source-parity gate

Before a future candidate is promoted to a public-release decision, compare the production proving-ground repository with the recorded parity point. A material reusable **core** delta reopens delegated extraction work. Host/transport adapters and site-only changes do not automatically block release.

Until that parity review and explicit release decisions occur, `0.1.0-rc.2` remains an internal review identity only.
