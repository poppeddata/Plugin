# Popped 2.1 architecture

Popped is a WordPress editorial archive and historical-timeline plugin built around one ownership rule:

> **Popped owns blocks. The active theme owns the site.**

Normal WordPress Posts, publication dates, featured images, categories and tags remain the source of truth. Popped does not introduce a custom event post type, duplicate event-date field or proprietary timeline table.

## Architecture

- `Popped_Block_Config` is the product catalogue and inherited-default layer for the 15 components.
- `blocks/*/block.json` is the canonical WordPress block-registration metadata. PHP and the editor consume that metadata instead of maintaining parallel attribute schemas.
- `Popped_Query` is the bounded reusable content-source system for all posts, Timeline posts, taxonomies and deliberate manual selections.
- `Popped_Components` renders dynamic visitor-facing blocks and optional article-discovery components.
- `Popped_Blocks` registers metadata-backed dynamic blocks, patterns and editor assets. Only interaction-heavy blocks declare the front-end `popped` view script.
- `Popped_Setup` creates or repairs only the optional Timeline, Archive and Search pages and the configured Timeline tag. It never changes Reading Settings, navigation, site templates, page-template assignments or the site name.
- `Popped_Templates` is a theme-coexistence layer. It retires legacy Popped-owned 2.0.x template records and provides an explicitly opt-in classic-theme shell for compatibility; block themes and the homepage are never replaced.
- `Popped_Admin` is organised around Setup, Design, Components, Collections, Templates / Display and Advanced.

## Ownership guarantees

- No custom post types or duplicate editorial records.
- No automatic `wp_template` creation.
- No homepage or navigation ownership.
- No page-template assignment by Setup.
- No global article-content append by default.
- No global search mutation by default.
- Legacy template cleanup touches only records carrying Popped's ownership marker.
- Uninstall preserves editorial content and configuration while removing obsolete Popped-owned presentation overrides.

## Block editor

Popped Homepage, Timeline, Horizontal Timeline, Mini Timeline, On This Day, Also On This Day, Continue the Story, Timeline Previous / Next, Related Stories, News Ticker, Latest Stories, Archive Explorer, Year Navigator, Featured Collection and Search are dynamic Gutenberg blocks.

Editor controls prefer native WordPress primitives:

- WordPress link control for destinations.
- Theme/global colour palettes rather than raw hexadecimal fields.
- Theme-exposed font-family choices rather than free-form CSS family input.
- Configurable semantic heading levels for reusable story and section headings.
- Global Styles/block supports for standard spacing, typography, colour and border capabilities.
- Server-rendered previews for data-backed components.

## Performance

- No jQuery or animation framework.
- View scripts load only for interactive rails/ticker components.
- Queries are bounded; visitor year controls have hard caps.
- Random ordering is not exposed and legacy random values fall back deterministically rather than using `ORDER BY RAND()`.
- On This Day and year-count caches include a content-version key incremented when posts or terms change.
- Native responsive images are used; eager/high-priority loading is reserved for the lead image.

## Accessibility and internationalisation

- User-selectable heading levels support logical page hierarchy.
- Horizontal rails expose named regions and keyboard navigation.
- Pointer drag starts only after a movement threshold and handles cancel/lost-capture events.
- RTL scrolling and logical CSS properties are used for directional layout.
- Site date-format settings are respected for editorial metadata.
- Motion controls and `prefers-reduced-motion` are respected.

## Validation

`tools/release_check.py` is the static/package contract. `tools/wordpress_smoke.php` runs inside a real WordPress installation. `specs/popped.spec.js` is a Playwright browser suite using WordPress's maintained E2E tooling.

GitHub Actions runs the static contract, a WordPress/PHP smoke matrix, official Plugin Check and browser E2E tests. A package should only be promoted from release candidate after those hosted jobs pass.
