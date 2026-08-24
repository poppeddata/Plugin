#!/usr/bin/env python3
"""Static and package-level release contract for Popped."""

from __future__ import annotations

import json
import os
import re
import shutil
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
EXPECTED_VERSION = "2.1.0"
EXPECTED_BLOCK_COUNT = 15
EXPECTED_PATTERN_COUNT = 17

INTERACTIVE_BLOCKS = {
    "also-on-this-day",
    "featured-collection",
    "homepage",
    "horizontal-timeline",
    "latest-stories",
    "mini-timeline",
    "news-ticker",
    "related-stories",
    "timeline",
}


class CheckFailure(RuntimeError):
    """Raised when a release contract fails."""


def fail(message: str) -> None:
    raise CheckFailure(message)


def read(relative: str) -> str:
    return (ROOT / relative).read_text(encoding="utf-8")


def run(command: list[str]) -> str:
    result = subprocess.run(
        command,
        cwd=ROOT,
        text=True,
        capture_output=True,
        check=False,
    )
    if result.returncode:
        output = "\n".join(
            item for item in (result.stdout, result.stderr) if item
        ).strip()
        fail(f"{' '.join(command)} failed:\n{output}")
    return result.stdout.strip()


def function_body(source: str, name: str) -> str:
    match = re.search(
        rf"(?:public|private|protected)?\s*(?:static\s+)?function\s+{re.escape(name)}\s*\(",
        source,
    )
    if not match:
        fail(f"Could not locate function {name}().")
    brace = source.find("{", match.end())
    if brace < 0:
        fail(f"Could not locate opening brace for {name}().")

    depth = 0
    quote = None
    escaped = False
    for index in range(brace, len(source)):
        char = source[index]
        if escaped:
            escaped = False
            continue
        if quote and char == "\\":
            escaped = True
            continue
        if char in ("'", '"'):
            if quote == char:
                quote = None
            elif quote is None:
                quote = char
            continue
        if quote:
            continue
        if char == "{":
            depth += 1
        elif char == "}":
            depth -= 1
            if depth == 0:
                return source[brace + 1:index]
    fail(f"Could not locate closing brace for {name}().")
    return ""


def check_release_metadata() -> None:
    plugin = read("popped.php")
    readme = read("readme.txt")
    changelog = read("CHANGELOG.md")
    testing = read("TESTING.md")
    package = json.loads(read("package.json"))

    header = re.search(r"^\s*\*\s*Version:\s*([^\s]+)", plugin, re.MULTILINE)
    constant = re.search(r"define\(\s*'POPPED_VERSION'\s*,\s*'([^']+)'\s*\)", plugin)
    stable = re.search(r"^Stable tag:\s*(\S+)", readme, re.MULTILINE)
    values = {
        "plugin header": header.group(1) if header else "",
        "POPPED_VERSION": constant.group(1) if constant else "",
        "Stable tag": stable.group(1) if stable else "",
        "package.json": package.get("version", ""),
    }
    bad = {key: value for key, value in values.items() if value != EXPECTED_VERSION}
    if bad:
        fail(f"Release metadata mismatch: {bad}")
    if f"## {EXPECTED_VERSION}" not in changelog:
        fail("CHANGELOG.md is missing the current release.")
    changelog_versions = re.findall(r"^##\\s+(\\d+\\.\\d+\\.\\d+)", changelog, re.MULTILINE)
    duplicates = sorted({version for version in changelog_versions if changelog_versions.count(version) > 1})
    if duplicates:
        fail(f"CHANGELOG.md contains duplicate release headings: {duplicates}.")
    if f"# Popped {EXPECTED_VERSION} release validation" not in testing:
        fail("TESTING.md does not identify the current release.")
    if "Twenty Twenty-Five" in plugin:
        fail("Plugin header is still tied to a specific theme.")
    print("PASS release metadata + theme-neutral plugin header")


def check_block_metadata() -> None:
    block_files = sorted((ROOT / "blocks").glob("*/block.json"))
    if len(block_files) != EXPECTED_BLOCK_COUNT:
        fail(f"Expected {EXPECTED_BLOCK_COUNT} block.json files, found {len(block_files)}.")

    names = set()
    interactive = set()
    for path in block_files:
        data = json.loads(path.read_text(encoding="utf-8"))
        slug = path.parent.name
        expected_name = f"popped/{slug}"
        if data.get("name") != expected_name:
            fail(f"{path.relative_to(ROOT)} has name {data.get('name')!r}, expected {expected_name!r}.")
        if data.get("apiVersion") != 3:
            fail(f"{expected_name} must use Block API v3.")
        if data.get("version") != EXPECTED_VERSION:
            fail(f"{expected_name} metadata version is stale.")
        if data.get("textdomain") != "popped":
            fail(f"{expected_name} is missing textdomain metadata.")
        attrs = data.get("attributes", {})
        for required in ("headingLevel", "sectionTitleLevel"):
            if required not in attrs:
                fail(f"{expected_name} is missing {required}.")
        if slug == "homepage" and "composition" not in attrs:
            fail("Homepage metadata is missing composition.")
        if data.get("editorScript") != "popped-blocks":
            fail(f"{expected_name} must use the registered editor script.")
        if data.get("style") != "popped":
            fail(f"{expected_name} must use the registered front-end style.")
        if data.get("viewScript") == "popped":
            interactive.add(slug)
        names.add(data["name"])

    if interactive != INTERACTIVE_BLOCKS:
        fail(
            "Selective view-script set is wrong: "
            f"expected {sorted(INTERACTIVE_BLOCKS)}, got {sorted(interactive)}."
        )

    php = read("includes/class-popped-blocks.php")
    if "register_block_type_from_metadata" not in php:
        fail("Blocks are not registered from block.json metadata.")
    if "private static function attributes" in php:
        fail("Legacy PHP attribute-schema duplication still exists.")
    js = read("assets/js/blocks.js")
    if re.search(r"\bvar\s+attributes\s*=\s*\{", js):
        fail("Legacy JavaScript attribute-schema duplication still exists.")
    if "config.metadata" not in js:
        fail("Editor registration does not consume server-localized metadata.")
    print(f"PASS block.json catalogue ({len(names)} blocks) + selective assets")


def check_theme_ownership() -> None:
    settings = read("includes/class-popped-settings.php")
    templates = read("includes/class-popped-templates.php")
    setup = read("includes/class-popped-setup.php")
    plugin = read("includes/class-popped-plugin.php")
    uninstall = read("uninstall.php")

    defaults = function_body(settings, "defaults")
    for key in ("template_mode", "append_discovery", "taxonomy_search"):
        pattern = rf"'{key}'\s*=>\s*false"
        if not re.search(pattern, defaults):
            fail(f"{key} must default to false.")

    setup_execute = function_body(setup, "execute")
    forbidden_setup = (
        "show_on_front",
        "page_on_front",
        "wp_create_nav_menu",
        "wp_update_nav_menu",
        "_wp_page_template",
        "wp_template",
        "blogname",
    )
    for token in forbidden_setup:
        if token in setup_execute:
            fail(f"Setup still takes site ownership via {token!r}.")

    if "register_block_templates" not in templates:
        fail("Backwards-compatible no-op template API is missing.")
    register_body = function_body(templates, "register_block_templates")
    if register_body.strip():
        fail("register_block_templates() must remain a no-op in 2.1.")
    if "_popped_template" not in function_body(templates, "release_legacy_templates"):
        fail("Legacy template release is not ownership-marker scoped.")
    if "front-page" not in function_body(templates, "release_legacy_templates"):
        fail("Legacy front-page cleanup is incomplete.")
    if "release_legacy_templates" not in plugin:
        fail("Upgrade cleanup is not registered.")
    if "_popped_template" not in uninstall or "wp_delete_post" not in uninstall:
        fail("Uninstall does not remove legacy Popped-owned templates.")
    if "delete_option( Popped_Settings::OPTION" in uninstall:
        fail("Uninstall unexpectedly deletes editorial configuration.")
    print("PASS theme ownership + targeted legacy cleanup")


def check_editor_native_controls() -> None:
    js = read("assets/js/blocks.js")
    required = (
        "wp.blockEditor.LinkControl",
        "wp.blockEditor.useSettings",
        "typography.fontFamilies",
        "color.palette",
        "ColorPalette",
        "ThemeFontControl",
        "NativeLinkField",
        "HeadingLevelControl",
        "sectionTitleLevel",
        "headingLevel",
    )
    for token in required:
        if token not in js:
            fail(f"Native editor UX contract is missing {token}.")
    if "wp.blockEditor.LinkControl || wp.blockEditor.__experimentalLinkControl" not in js:
        fail("Stable LinkControl is not preferred before the compatibility fallback.")
    if "Archive destination" in js and "NativeLinkField" not in js:
        fail("Archive destination is not using the native link control.")
    # Exact colour fields must not revert to raw TextControl inputs.
    if re.search(r"el\(\s*TextControl\s*,\s*\{[^}]*label:\s*__\(\s*'(?:Title|Excerpt|Metadata|Section heading|Primary text|Secondary text|Accent) colour", js, re.S):
        fail("A raw text colour field remains in the block editor.")
    raw_ui = re.findall(
        r"\b(?:label|help|title|placeholder|message)\s*:\s*(['\"])([A-Za-z][^'\"]*)\1",
        js,
    )
    untranslated = [value for _, value in raw_ui if value not in {"H"}]
    if untranslated:
        fail(f"Editor UI contains untranslated literal strings: {untranslated[:5]}.")
    if "Popped → Setup" in js:
        fail("Homepage editor guidance still points to the old Setup location.")
    print("PASS stable Gutenberg link/colour/font/heading controls + editor gettext")

def check_search_and_query_boundaries() -> None:
    templates = read("includes/class-popped-templates.php")
    components = read("includes/class-popped-components.php")
    query = read("includes/class-popped-query.php")
    settings = read("includes/class-popped-settings.php")
    admin = read("includes/class-popped-admin.php")

    if "_popped_search" not in components or "_popped_search" not in templates:
        fail("Popped Search requests are not explicitly scoped.")
    if "taxonomy_search" not in templates:
        fail("Global taxonomy-search opt-in is not wired.")
    if "ORDER BY RAND" in query.upper() or re.search(r"['\"]orderby['\"]\s*=>\s*['\"]rand", query, re.I):
        fail("Query layer still uses ORDER BY RAND().")
    for source_name, source in (("settings", settings), ("admin", admin), ("editor", read("assets/js/blocks.js"))):
        if re.search(r"['\"]Random['\"]|\bRandom\b", source):
            fail(f"Random ordering remains exposed in {source_name}.")
    if components.count("'number' => 200") < 4:
        fail("Large taxonomy controls are not bounded.")
    print("PASS scoped search + bounded taxonomy/query behavior")


def check_cache_invalidation() -> None:
    plugin = read("includes/class-popped-plugin.php")
    query = read("includes/class-popped-query.php")
    components = read("includes/class-popped-components.php")
    if "popped_content_cache_version" not in plugin:
        fail("Content cache invalidation version is missing.")
    if "popped_content_cache_version" not in query:
        fail("On This Day cache key is not versioned.")
    if "popped_content_cache_version" not in components:
        fail("Year-count cache key is not versioned.")
    for hook in ("save_post_post", "deleted_post", "set_object_terms"):
        if hook not in plugin:
            fail(f"Cache invalidation hook {hook} is missing.")
    print("PASS content-derived cache invalidation")


def check_accessibility_rtl_dates() -> None:
    components = read("includes/class-popped-components.php")
    js = read("assets/js/popped.js")
    css = read("assets/css/popped.css")
    if "sectionTitleLevel" not in components or "headingLevel" not in components:
        fail("Renderer does not honor configurable heading levels.")
    if "Scrollable timeline stories" not in components or 'role="region"' not in components:
        fail("Horizontal Timeline is not exposed as a named region.")
    if "get_option( 'date_format'" not in components:
        fail("Story metadata does not honor the WordPress date format.")
    if "get_option( 'date_format'" not in read("templates/single.php"):
        fail("Legacy single shell does not honor the WordPress date format.")
    if "rtl" not in js.lower() or "direction" not in js:
        fail("Rail JavaScript is not RTL-aware.")
    if "pointercancel" not in js or "lostpointercapture" not in js:
        fail("Pointer-drag cancellation handling is incomplete.")
    if "6" not in js[js.find("pointermove"):js.find("pointerup")]:
        fail("Pointer drag threshold contract is missing.")
    if "popped-rail-control__icon" not in components or 'aria-hidden="true"' not in components:
        fail("Rail arrow glyphs are not decorative with logical accessible names.")
    if ":dir(rtl) .popped-rail-control__icon" not in css:
        fail("Rail control icons are not mirrored for RTL.")
    if "reduceMotion" in js or "prefersReducedMotion()" not in js:
        fail("Reduced-motion behavior is cached or not queried at interaction time.")
    physical = ("margin-left", "margin-right", "padding-left", "padding-right", "border-left", "border-right")
    leftovers = [token for token in physical if token in css]
    if leftovers:
        fail(f"Physical directional CSS properties remain: {leftovers}.")
    print("PASS heading semantics + date localization + RTL/pointer resilience")


def check_year_and_layout_contracts() -> None:
    components = read("includes/class-popped-components.php")
    blocks_js = read("assets/js/blocks.js")
    css = read("assets/css/popped.css")

    for token in ("MAX_RENDERED_YEARS = 100", "MAX_FITTED_ROW_YEARS = 12"):
        if token not in components:
            fail(f"Missing year safety contract: {token}.")
    if "Single row (fit)" not in blocks_js:
        fail("Year Navigator one-line fit option is missing.")
    if "minmax(0,1fr)" not in css.replace(" ", ""):
        fail("Shrink-safe minmax(0, 1fr) grid columns are missing.")
    if "min-width:0" not in css.replace(" ", ""):
        fail("Layout resilience min-width:0 guards are missing.")
    print("PASS year navigator + layout resilience")



def check_runtime_theme_integration() -> None:
    plugin = read("includes/class-popped-plugin.php")
    blocks = read("includes/class-popped-blocks.php")
    components = read("includes/class-popped-components.php")
    settings = read("includes/class-popped-settings.php")
    admin = read("includes/class-popped-admin.php")

    if "wp_add_inline_style( 'popped', Popped_Plugin::design_token_css() )" not in blocks:
        fail("Design tokens are not attached to the shared block stylesheet.")
    if "wp_head" in plugin and "design_tokens" in plugin:
        fail("Legacy request-guessing design-token wp_head injection remains.")
    if "should_load_design_tokens" in plugin:
        fail("Legacy request-guessing design-token logic remains.")

    for function_name in ("homepage", "homepage_section_stack"):
        body = function_body(components, function_name)
        if "ticker_placement" in body or "ticker_enabled" in body:
            fail(f"{function_name}() still injects a ticker implicitly.")

    if "Inject ticker into legacy Popped shell" not in admin:
        fail("Ticker admin UI does not explain legacy-shell-only injection.")
    if "below-hero" in admin or "'homepage' =>" in admin:
        fail("Obsolete homepage ticker-placement choices remain in the admin UI.")
    sanitize_body = function_body(settings, "sanitize")
    placement_match = re.search(
        r"ticker_placement'.*?array\(\s*'below-header',\s*'above-footer'\s*\)",
        sanitize_body,
        re.S,
    )
    if not placement_match:
        fail("Ticker placement sanitizer still accepts obsolete global homepage placements.")
    if "Popped → Components" not in read("assets/js/blocks.js"):
        fail("Homepage block guidance does not point to Components.")
    print("PASS Site Editor design tokens + explicit ticker placement ownership")


def check_release_engineering() -> None:
    required = (
        "package.json",
        ".wp-env.json",
        "playwright.config.js",
        "specs/popped.spec.js",
        "tools/wordpress_smoke.php",
        ".github/workflows/quality.yml",
    )
    for relative in required:
        if not (ROOT / relative).is_file():
            fail(f"Missing release-engineering file: {relative}")

    package = json.loads(read("package.json"))
    scripts = package.get("scripts", {})
    deps = package.get("devDependencies", {})
    if scripts.get("test:e2e") != "wp-scripts test-e2e":
        fail("package.json does not expose the WordPress Playwright command.")
    for dep in (
        "@playwright/test",
        "@wordpress/e2e-test-utils-playwright",
        "@wordpress/env",
        "@wordpress/scripts",
    ):
        if dep not in deps:
            fail(f"Missing E2E dependency: {dep}")
        if not re.fullmatch(r"\d+\.\d+\.\d+", str(deps[dep])):
            fail(f"E2E dependency {dep} must be pinned to an exact version.")

    spec = read("specs/popped.spec.js")
    for phrase in (
        "complete Popped block catalogue",
        "every Popped block without an editor runtime error",
        "persists its key UX settings",
        "page-level horizontal overflow",
        "design tokens with Popped blocks while leaving the theme shell alone",
        "keyboard-accessible as a named region",
    ):
        if phrase not in spec:
            fail(f"E2E suite is missing coverage for: {phrase}")
    workflow = read(".github/workflows/quality.yml")
    for job in ("release-contract:", "wordpress-smoke:", "browser-e2e:", "plugin-check:"):
        if job not in workflow:
            fail(f"CI workflow is missing {job}")
    if "slug: 'popped'" not in workflow:
        fail("Plugin Check must receive the canonical popped slug.")
    if "actions/checkout@v4" in workflow or "actions/setup-node@v4" in workflow:
        fail("CI still uses deprecated Node 20 GitHub Actions.")
    smoke = read("tools/wordpress_smoke.php")
    if "native_templates()" in smoke:
        fail("Smoke suite calls the removed native_templates() API.")
    if re.search(r"Expected Popped 2\.\d", smoke):
        fail("Smoke suite contains a hard-coded patch-version assertion.")
    if "function popped_smoke_run()" not in smoke:
        fail("Smoke-test execution must be wrapped so test variables are not plugin globals.")

    setup = read("includes/class-popped-setup.php")
    if "'meta_key'       => '_popped_page_role'" in setup or "'meta_value'     => $role" in setup:
        fail("Setup still uses a slow post-meta recovery query.")

    admin = read("includes/class-popped-admin.php")
    if "array_map( 'absint', (array) wp_unslash( $_POST['popped_related_posts'] ) )" not in admin:
        fail("Related-story POST IDs are not explicitly sanitized at the request boundary.")
    if "array_map( 'absint', (array) wp_unslash( $_POST['popped_related_exclude'] ) )" not in admin:
        fail("Excluded-story POST IDs are not explicitly sanitized at the request boundary.")

    components = read("includes/class-popped-components.php")
    if "sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) )" not in components:
        fail("REQUEST_URI is not sanitized before URL parsing.")
    if "phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery" not in components:
        fail("The intentional cached year-count aggregate query lacks a narrow PHPCS justification.")

    print("PASS automated smoke/E2E/Plugin Check CI + warning-free certification contracts")


def check_security_contract() -> None:
    php_files = list(ROOT.rglob("*.php"))
    combined = "\n".join(path.read_text(encoding="utf-8") for path in php_files)
    dangerous = (
        r"\bshell_exec\s*\(",
        r"\bexec\s*\(",
        r"\bsystem\s*\(",
        r"\bpassthru\s*\(",
        r"\bproc_open\s*\(",
        r"\bunserialize\s*\(",
    )
    for pattern in dangerous:
        if re.search(pattern, combined):
            fail(f"Security guardrail matched {pattern}.")
    query = read("includes/class-popped-query.php")
    if "posts_per_page' => -1" in query or 'posts_per_page" => -1' in query:
        fail("Public query layer contains an unbounded post query.")
    print("PASS static security/query guardrails")


def check_package_hygiene() -> None:
    forbidden_dirs = {".git", "node_modules", "__pycache__", ".idea", ".vscode"}
    forbidden_files = {".DS_Store", "Thumbs.db"}
    allow_ci_git_metadata = os.environ.get("GITHUB_ACTIONS") == "true"
    for path in ROOT.rglob("*"):
        relative = path.relative_to(ROOT)
        if allow_ci_git_metadata and relative.parts and relative.parts[0] == ".git":
            continue
        if any(part in forbidden_dirs for part in relative.parts):
            fail(f"Forbidden development directory in package: {relative}")
        if path.name in forbidden_files:
            fail(f"Forbidden file in package: {relative}")
        if path.is_file() and path.suffix.lower() in {".zip", ".tar", ".gz"}:
            fail(f"Nested archive in package: {relative}")
    print("PASS package hygiene")


def check_php_syntax() -> None:
    php = shutil.which("php")
    files = sorted(ROOT.rglob("*.php"))
    if not php:
        print("SKIP PHP syntax (php unavailable)")
        return
    for path in files:
        run([php, "-l", str(path)])
    print(f"PASS PHP syntax ({len(files)} files)")


def check_javascript_syntax() -> None:
    node = shutil.which("node")
    files = sorted((ROOT / "assets/js").glob("*.js")) + sorted((ROOT / "specs").glob("*.js")) + [ROOT / "playwright.config.js"]
    if not node:
        print("SKIP JavaScript syntax (node unavailable)")
        return
    for path in files:
        run([node, "--check", str(path)])
    print(f"PASS JavaScript syntax ({len(files)} files)")


def strip_css_comments(source: str) -> str:
    return re.sub(r"/\*.*?\*/", "", source, flags=re.S)


def check_css_structure() -> None:
    files = sorted((ROOT / "assets/css").glob("*.css"))
    for path in files:
        source = strip_css_comments(path.read_text(encoding="utf-8"))
        if source.count("{") != source.count("}"):
            fail(f"Unbalanced CSS braces: {path.relative_to(ROOT)}")
    print(f"PASS CSS structure ({len(files)} files)")


def check_pattern_nesting() -> None:
    files = sorted((ROOT / "patterns").glob("*.php"))
    if len(files) != EXPECTED_PATTERN_COUNT:
        fail(f"Expected {EXPECTED_PATTERN_COUNT} patterns, found {len(files)}.")
    token_re = re.compile(r"<!--\s*(/?)wp:([a-z0-9-]+(?:/[a-z0-9-]+)?)\b[^>]*?(\/?)-->")
    for path in files:
        stack: list[str] = []
        source = path.read_text(encoding="utf-8")
        for match in token_re.finditer(source):
            closing, name, self_closing = match.groups()
            if self_closing:
                continue
            if closing:
                if not stack or stack[-1] != name:
                    fail(f"Pattern nesting mismatch in {path.name}: closing {name}, stack={stack}.")
                stack.pop()
            else:
                stack.append(name)
        if stack:
            fail(f"Pattern {path.name} has unclosed blocks: {stack}.")
    print(f"PASS Gutenberg pattern nesting ({len(files)} files)")


def main() -> int:
    checks = (
        check_release_metadata,
        check_block_metadata,
        check_theme_ownership,
        check_editor_native_controls,
        check_search_and_query_boundaries,
        check_cache_invalidation,
        check_accessibility_rtl_dates,
        check_year_and_layout_contracts,
        check_runtime_theme_integration,
        check_release_engineering,
        check_security_contract,
        check_package_hygiene,
        check_php_syntax,
        check_javascript_syntax,
        check_css_structure,
        check_pattern_nesting,
    )
    try:
        for check in checks:
            check()
    except (CheckFailure, json.JSONDecodeError, OSError) as error:
        print(f"FAIL {error}", file=sys.stderr)
        return 1

    print(f"\nPopped {EXPECTED_VERSION} release contract passed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
