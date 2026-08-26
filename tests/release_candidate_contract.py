#!/usr/bin/env python3
from __future__ import annotations

import hashlib
import importlib.util
import json
from pathlib import Path
import tempfile
import zipfile

ROOT = Path(__file__).resolve().parents[1]


def fail(message: str) -> None:
    print(f"FAIL: {message}")
    raise SystemExit(1)


def load_builder():
    path = ROOT / "tools/build_release.py"
    if not path.is_file():
        fail("release candidate builder is missing")
    spec = importlib.util.spec_from_file_location("aincms_build_release", path)
    if spec is None or spec.loader is None:
        fail("release candidate builder could not be loaded")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def main() -> None:
    version = (ROOT / "VERSION").read_text(encoding="utf-8").strip()
    if version != "0.1.0-rc.1":
        fail("unexpected internal release candidate version")

    metadata = json.loads((ROOT / "release/release.json").read_text(encoding="utf-8"))
    if metadata.get("version") != version:
        fail("VERSION and release metadata disagree")
    distribution = metadata.get("distribution", {})
    if distribution.get("public") is not False or distribution.get("licenseSelected") is not False:
        fail("release candidate metadata crossed the public/license boundary")

    workflow = (ROOT / ".github/workflows/ci.yml").read_text(encoding="utf-8")
    if "github.event.pull_request.head.sha || github.sha" not in workflow:
        fail("CI release artifact does not record the reviewed PR head or main push SHA")

    builder = load_builder()
    candidates = [path.relative_to(ROOT).as_posix() for path in builder.candidate_files()]
    required = {
        "README.md", "SECURITY.md", "VERSION", "release/release.json",
        "api/runtime.php", "cms/pages.php", "config/site.example.php",
        "database/schema.sql", "database/bootstrap.php", "database/private-config.example.ini",
        "docs/ARCHITECTURE.md", "docs/INSTALLATION.md", "docs/RELEASE.md",
    }
    missing = sorted(required.difference(candidates))
    if missing:
        fail("release candidate is missing required files: " + ", ".join(missing))

    forbidden_prefixes = (".git/", ".github/", ".lattice/", "tests/", "tools/", "dist/", "runtime/", "uploads/")
    for path in candidates:
        if path == "config/site.php" or path.startswith(forbidden_prefixes):
            fail("excluded operational/adopter path entered candidate: " + path)
        if path.endswith(".ini") and path != "database/private-config.example.ini":
            fail("non-example INI entered candidate: " + path)

    with tempfile.TemporaryDirectory() as tmp:
        first = Path(tmp) / "one"
        second = Path(tmp) / "two"
        result_a = builder.build(first, "release-contract-ref")
        result_b = builder.build(second, "release-contract-ref")
        zip_a = Path(result_a["zip"]).read_bytes()
        zip_b = Path(result_b["zip"]).read_bytes()
        if zip_a != zip_b:
            fail("release candidate ZIP is not byte-for-byte reproducible")
        if hashlib.sha256(zip_a).hexdigest() != result_a["sha256"]:
            fail("reported release checksum does not match candidate ZIP")
        manifest_a = json.loads(Path(result_a["manifest"]).read_text(encoding="utf-8"))
        manifest_b = json.loads(Path(result_b["manifest"]).read_text(encoding="utf-8"))
        if manifest_a != manifest_b:
            fail("release manifests are not reproducible")
        if manifest_a.get("sourceRevision") != "release-contract-ref":
            fail("release manifest lost source revision")
        if manifest_a.get("schemaVersion") != 7:
            fail("release manifest schema version is wrong")
        with zipfile.ZipFile(result_a["zip"], "r") as archive:
            names = archive.namelist()
            root = metadata["packageRoot"].rstrip("/") + "/"
            if root + "RELEASE-MANIFEST.json" not in names:
                fail("candidate ZIP does not contain its release manifest")
            if any(name.startswith(root + prefix) for prefix in forbidden_prefixes for name in names):
                fail("candidate ZIP contains an excluded operational path")
            if root + "config/site.php" in names:
                fail("candidate ZIP contains adopter-local config/site.php")
            if any(name.rsplit("/", 1)[-1].lower().startswith("license") for name in names):
                fail("candidate contains a license even though licenseSelected is false")

    installation = (ROOT / "docs/INSTALLATION.md").read_text(encoding="utf-8")
    release_doc = (ROOT / "docs/RELEASE.md").read_text(encoding="utf-8")
    for needle in ["database/bootstrap.php", "database/reconcile.php initial-import", "database/readiness.php", "backup", "rollback", "migration"]:
        if needle.lower() not in installation.lower():
            fail("installation/upgrade documentation is missing: " + needle)
    for needle in ["internal release candidate", "not a public release", "license", "tag", "tools/build_release.py", "sha256"]:
        if needle.lower() not in release_doc.lower():
            fail("release-boundary documentation is missing: " + needle)

    print("PASS: reproducible internal release candidate contract")


if __name__ == "__main__":
    main()
