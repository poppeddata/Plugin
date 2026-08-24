# Popped 2.1.0 release validation

## Static/package contract

Run from the plugin root:

```bash
python3 tools/release_check.py
```

The contract verifies release metadata, all 15 `block.json` files, dynamic server registration, theme-ownership defaults, Setup boundaries, legacy-template cleanup, selective view-script declarations, stable/native editor controls, editor gettext, Site Editor design-token delivery, explicit ticker ownership, heading-level support, cache invalidation, bounded queries/filters, layout resilience, security guardrails, package hygiene, PHP/JavaScript syntax, CSS structure and all 17 Gutenberg patterns.

## Live WordPress smoke suite

Run inside a booted WordPress installation:

```bash
wp eval-file wp-content/plugins/popped/tools/wordpress_smoke.php
```

The smoke suite verifies:

- plugin/header version consistency;
- all 15 metadata-backed Popped blocks;
- selective front-end view-script loading and design tokens attached to the shared block stylesheet;
- legacy template shell, global discovery append and global taxonomy search are off by default;
- Setup leaves Reading Settings untouched;
- Setup creates published Timeline/Archive/Search pages without assigning Popped page templates;
- trashed managed pages are replaced rather than reused;
- legacy Popped-owned database templates are retired without touching theme/user ownership.

GitHub Actions runs this against WordPress 6.7.7/PHP 7.4 and WordPress 7.1/PHP 8.3 + 8.4.

## Browser E2E

The repository includes WordPress's Playwright tooling:

```bash
npm install
npx playwright install --with-deps chromium
npm run test:e2e
```

`specs/popped.spec.js` covers:

- registration of the complete 15-block catalogue in the editor;
- inserting all 15 Popped blocks together without editor runtime errors;
- inserting Year Navigator through the Gutenberg inserter;
- save/reload persistence of its one-line fit, limit, ordering and heading-level settings;
- page-level overflow resistance with long editorial headings;
- design-token delivery without injecting a Popped theme shell;
- the Horizontal Timeline's named keyboard-focusable region.

## Official Plugin Check

GitHub Actions also invokes `wordpress/plugin-check-action@v1` against WordPress 7.1.

## Release-status rule

Local static/package success is **not** equivalent to hosted certification. A production/public-directory release requires all configured GitHub Actions jobs—static contract, WordPress smoke matrix, browser E2E and Plugin Check—to be green.

Historical audit documents retained in the repository are engineering history only; they are not evidence that 2.1.0 passed current live tests.
