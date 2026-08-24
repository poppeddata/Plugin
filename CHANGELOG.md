# Popped changelog

## 2.1.0
- Reframed the plugin around a strict **Popped owns blocks, the theme owns the site** contract.
- Removed creation/repair of database-backed `wp_template` overrides for Single, Archive, Search, 404 and Front Page; upgrades retire only records carrying Popped's ownership marker.
- Added targeted uninstall cleanup for those legacy presentation records while preserving editorial posts, archive pages, terms and settings.
- Made the classic-theme shell, appended article discovery and site-wide taxonomy-aware search explicit opt-ins; Popped Search requests remain taxonomy-aware without mutating ordinary theme search.
- Reduced Setup to optional Timeline, Archive and Search pages plus the Timeline tag; Setup no longer changes the site name, homepage, navigation or page-template assignment.
- Migrated all 15 dynamic blocks to canonical `block.json` registration and limited the front-end interaction script to blocks that actually require it.
- Replaced raw block URL, colour and font-family fields with WordPress link controls, theme palettes and theme-exposed font choices; current WordPress uses stable `LinkControl` and `useSettings()` while WordPress 6.7 retains compatibility fallbacks.
- Attached design tokens to the shared Popped block stylesheet so blocks inherit the configured design inside ordinary content, Site Editor templates and Query contexts without request-guessing `wp_head` injection.
- Removed implicit homepage ticker placement: editors place a News Ticker block or enable the Homepage block's News Ticker section; automatic header/footer ticker injection exists only inside the opt-in legacy Popped shell.
- Added configurable story/section heading levels, site date formatting, bounded term filters, RTL-aware rail behavior, mirrored decorative rail icons and drag thresholds/cancel handling.
- Removed Random ordering from user-facing controls and retained only a deterministic newest-first fallback for legacy saved values, eliminating `ORDER BY RAND()` for large archives.
- Versioned content-derived cache keys so On This Day and year-count data refresh after post/taxonomy changes even with persistent object caches.
- Added Playwright browser tests for all-block registration and insertion, Year Navigator save/reload persistence, long-content overflow resilience, theme-shell ownership/design-token delivery and Horizontal Timeline accessibility.
- Strengthened live WordPress smoke tests for selective asset loading, theme ownership, Setup behavior and legacy-template cleanup.

## 2.0.8
- Hardened every Popped block family against long titles, custom labels, translated controls and narrow parent containers.
- Capped Year Navigator **Single row (fit)** at 12 rendered years so the desktop row cannot silently collapse into overlapping labels; longer ranges expose the full-archive link.
- Made section headings/actions, story lists, vertical timelines, archive timelines, discovery cards and homepage lead copy shrink safely instead of forcing horizontal page overflow.
- Fixed Featured Collections rows so image, copy and arrow use explicit grid columns rather than an implicit third column.
- Allowed filter/search action labels and ticker controls to wrap safely when translations or custom labels are unusually long.
- Preserved intentional horizontal scrolling for story rails, horizontal timelines and static/reduced-motion tickers while preventing accidental page-level overflow elsewhere.
- Added release-contract coverage for layout resilience and Featured Collection row markup.
- Removed the stale hard-coded 2.0.5 assertion from the live WordPress smoke suite; it now verifies `POPPED_VERSION` against the plugin header so future patch releases cannot invalidate CI metadata.

## 2.0.7
- Changed Year Navigator **Single row** to fit its year items evenly across one desktop line instead of requiring horizontal scrolling.
- Kept one-row horizontal touch scrolling below 600px so labels remain readable on narrow screens.
- Updated the editor copy and release contract to match the fitted desktop behavior.

## 2.0.6
- Added a first-class Year Navigator **Single row (scroll)** presentation so years can stay on one horizontal line without custom CSS.
- Clarified the previous inline presentation as **Inline (wrap)**.
- Added Year Navigator wide-screen column control (2–6 columns) and year-order control (newest/oldest first).
- Aligned Year Navigator editor limits with the supported 1000–3000 year range and the 100-item render safety cap.
- Replaced the misleading implicit year-limit slider with an explicit **Limit years shown** toggle so existing unlimited blocks report their real state.
- Moved story and section-heading alignment out of the buried Exact type & colours panel into the primary Design panel while retaining the block-toolbar shortcuts.
- Added regression coverage for Year Navigator presentation and UX contracts.

## 2.0.5
- Removed Popped ownership of the WordPress front page and stopped registering or creating a `front-page` template.
- Setup no longer creates, repairs, or selects a homepage and never changes `show_on_front` or `page_on_front`.
- Legacy Popped-owned front-page templates are retired without touching user/theme-owned templates.
- Classic-theme fallbacks and managed-shell detection explicitly exclude the site front page.
- The Popped Homepage block and editorial homepage pattern remain available as optional building blocks.
- Added regression coverage for front-page ownership.

## 2.0.4
- Fixed Custom Setup so `typography=inherit` is accepted and remains the recommended fallback.
- Synchronized the Homepage `composition` attribute between server and editor schemas.
- Made `setup_complete` conditional on successful tag, page, homepage and navigation setup; page/menu helpers now propagate explicit success state.
- Reject trashed managed pages and surface page repair failures instead of treating them as ready.
- Moved textdomain registration to `init` priority 0 and internationalized block catalogue metadata, admin choices, homepage section labels and native 404 content.
- Replaced potentially huge visitor year loops with populated-year controls capped at 100 items while preserving selected historical years.
- Expanded `tools/release_check.py` to guard schema parity, setup state, page status, i18n, year bounds, security and package hygiene.
- Added `tools/wordpress_smoke.php`, live WordPress CI coverage and the official WordPress Plugin Check action.
- Rewrote `TESTING.md` so current-package evidence is clearly separated from historical audit material.
- Preserved 2.0.x stored content, block names and existing attribute compatibility.

## 2.0.3
- Restored the intended Timeline Previous / Next `utilityAlign` and `utilityGap` defaults by removing the earlier unreachable duplicate switch case.
- Registered JavaScript translations for the `popped-blocks` Gutenberg editor bundle.
- Added `tools/release_check.py` and a CI workflow to reproduce syntax, release-metadata and regression-contract checks.
- Kept the release data-compatible with 2.0.x saved blocks and normal WordPress post storage.

## 2.0.2
- Reworked the news ticker into a progressive-enhancement marquee with one semantic source group and measured visual clones, eliminating wide-screen/short-content gaps.
- Marked every visual clone `aria-hidden` and `inert`, removed cloned links from the tab order, and removed clones completely when the ticker is static or reduced motion is active.
- Added a persistent keyboard-accessible Pause / Resume control for moving tickers.
- Fixed `tickerPause` so the optional pause-on-hover/focus behaviour now respects the saved block setting.
- Added a classic-template skip link.
- Internationalised the remaining user-facing admin JavaScript strings.
- Synchronized client/server Gutenberg support declarations.
- Registered the Popped front-end stylesheet/script as block assets and limited explicit front-end enqueues to Popped-managed shells and automatic article discovery.
- Synchronized `readme.txt` Stable tag and release notes with version 2.0.2.
- Horizontal Timeline: added a native Show item count toggle and an optional Full timeline link.
- Removed the unintended tinted background from image wrappers and tightened the header-to-content gap.

## 2.0.0
- Finished the zero-CSS editing model across story and utility blocks.
- Added native alignment, exact type sizing, semantic colours, spacing and mobile overrides to utility blocks.
- Added quick block-toolbar alignment and preserved inherited defaults and existing saved block markup.
- Kept the inserter focused on reusable editorial patterns and added focused Timeline Explorer and Year Explorer patterns.

## 1.9.0
- Added best-in-class editorial presentations for story blocks: Balanced Grid, Lead + Supporting, Compact List, and Swipeable Rail.
- Latest Stories, Related Stories, and Featured Collection now share the same predictable presentation model.
- Added structural Lead + Supporting and rail rendering with responsive behaviour.
- Simplified redundant block-style choices so density/surface controls are not duplicated in the Styles picker.
- Refocused the pattern inserter on reusable editorial sections instead of complete page starters.
- Added Story Rail, Compact Story List, and Related Story Rail patterns.
- Upgraded Latest Stories and Featured Collection patterns to use stronger editorial compositions.
- Preserved legacy style CSS for existing saved content.

## 1.8.0 — Zero-CSS block controls

- Added exact native alignment controls for story copy and section headings.
- Added exact card-title, excerpt, metadata and section-heading sizes.
- Added per-element font-family and colour overrides without custom CSS.
- Added optional mobile title/excerpt/metadata/section-title sizing and 1/2-column overrides.
- Preserved designed presets, inherited defaults and existing block markup.


## 1.7.0 — Component-system rebuild

- Replaced the accumulated presentation cascade with a single compact responsive component system for Popped-owned UI.
- Simplified Gutenberg controls: content and behaviour stay in Settings; designed presets, density, images, type/metadata and optional fine tuning live in Styles.
- Removed native whole-block typography, min-height and border controls that duplicated or fought component-level controls.
- Added Editorial, Compact, Minimal and Cards design recipes with safe responsive behaviour.
- Rebuilt vertical timelines without alternating layouts, reduced horizontal rail widths, and made image-ratio changes layout-safe.
- Reworked On This Day, story grids/lists, filters, search, archive controls, year navigation, collections, ticker and discovery modules around compact laptop-first proportions.
- Changed the recommended homepage pattern into real individually editable Gutenberg sections.
- Reduced the pattern library to 12 curated patterns and removed obsolete duplicate/magazine variants.
- Preserved the normal-post data model, query architecture, saved content and existing block attributes.

## 1.6.2 — Compact defaults

- Reduced the standard Popped block rhythm so section padding, card gaps and media spacing fit ordinary laptop viewports more comfortably.
- Rebalanced medium/default heading scales for story cards, timelines, On This Day, search, discovery and homepage compositions.
- Reduced horizontal timeline card widths and vertical timeline spacing without changing query or saved-content architecture.
- Tightened curated pattern spacing and editor previews while preserving Gutenberg controls and 44px interactive touch targets.
- Kept Compact and Spacious density choices meaningful, but moved all three density tiers down to a calmer editorial scale.


## 1.6.1

- Restored block-theme ownership of native header/footer template parts and site identity so Twenty Twenty-Five remains the site shell.
- Consolidated the visual cascade into `assets/css/popped.css`; `polish.css` is now a non-enqueued compatibility placeholder, with conflicting property ownership removed from earlier rules where safe.
- Added a theme-inheriting typography mode and made it the new-install default without migrating or changing existing saved typography choices.
- Extended native Gutenberg block supports for font family/style/weight, letter spacing, text transform and minimum height.
- Simplified card spacing into Compact/Balanced/Airy recipes with exact controls hidden until requested.
- Made story grids calm by default and reserved lead-card asymmetry for explicit Editorial/Feature styles.
- Added news-ticker direction and separator controls and corrected ticker vertical centring.
- Reworked block-theme single/404 compositions, compacted classic fallback articles, and made search pagination/empty states more deliberate.
- Removed editor “Live preview” overlay chrome and kept Popped-specific preview affordances subordinate to content.
- Preserved existing block names, attributes, query/storage architecture and hidden legacy pattern registrations.

## 1.6.0

- Reframed Popped as an extension of Twenty Twenty-Five rather than a second theme: ordinary Gutenberg/theme content keeps its theme styling while Popped blocks and Popped-managed templates retain the plugin design system.
- Added `assets/css/polish.css` as a single coherence layer for responsive rhythm, story hierarchy, timelines, filters, On This Day media and article discovery.
- Rebuilt admin and editor chrome to reduce boxed framing, heavy hover states and preview-only decoration.
- Added Editorial, Compact, Feature and Soft cards appearance recipes in the Gutenberg inspector while retaining exact component controls and native block supports.
- Curated the pattern inserter to 12 primary patterns across Pages, Sections and Article categories. Existing legacy and magazine pattern registrations remain available for already-saved content but are hidden from new insertion.
- Re-authored the 12 curated patterns around functional dynamic blocks with no hard-coded hex colours or oversized promotional filler copy.
- Scoped body-level typography and reduced-motion selectors so activating Popped no longer changes unrelated theme content.
- Kept existing security-sensitive JavaScript and query logic unchanged; the visual pass does not add remote requests, custom REST routes or unbounded queries.

## 1.5.1

- Excludes password-protected posts from Popped discovery queries and prevents protected post bodies from being used as custom card excerpts.
- Removes heuristic count migrations that could overwrite intentionally configured values on upgrade.
- Fixes On This Day so it excludes the current year, finds preferred/override heroes beyond the first result slice, and reports the true number of matching historical stories.
- Fixes Horizontal Timeline result counts, locks the dedicated Horizontal Timeline block to horizontal mode, and removes unrelated visitor filters from that focused rail.
- Replaces per-year count queries with one cached grouped query for the visible year range.
- Prevents horizontal timeline wheel handling from trapping vertical trackpad scrolling at either end of the rail.
- Restricts discovery-meta references to posts the current editor can read.
- Generates request-unique IDs for repeatable Popped headings/search controls to avoid duplicate DOM IDs.
- No pattern, spacing, typography, colour, card-design, or CSS changes.

## 1.5.0

- Rebased content density around intentional editorial slices instead of maximum item counts.
- Changed recommended Timeline pagination from 18 to 10 stories, On This Day candidates from 8 to 4, Latest Stories from 6 to 5 and Related Stories from 4 to 3.
- Migrates only untouched 1.4.x default count values; explicitly customised counts are preserved.
- Reduced Horizontal Timeline to 6 cards, Mini Timeline to 4, Also On This Day to 4, News Ticker to 5 and Archive Explorer to 12 stories per page.
- Updated all 30 patterns to the same count contract and reduced oversized dynamic heading presets.
- Added a View all stories route to Latest Stories whenever more matching posts exist.
- Added a bounded Year Navigator default for new blocks and homepage/pattern use, with a View full archive route when the range is trimmed.
- Limited collection-index blocks to a configurable number of collections; new indexes default to 5 and the homepage uses 4.
- Replaced excessive block-editor count ranges with component-specific limits and clear recommended-count guidance.
- Added a Collections shown control for collection-index blocks and a Years shown control for Year Navigator.
- Added a direct On This Day Feature image size control (Compact / Standard / Large), with Compact as the recommended responsive default.
- Reduced the largest authored pattern mastheads so pattern chrome no longer competes with the dynamic content.
- Added `FINAL-USABILITY-AUDIT.md` documenting the final default-length and continuation contract.

## 1.4.4

- Made viewport economy part of the standard design system rather than an exceptional laptop-only patch.
- Reduced the Standard density scale by roughly 15% across section padding, page edges, inter-section flow, grids, rails, filters and archive controls while preserving 44px interactive targets.
- Added explicit Compact and Spacious density behavior on phones so all three density modes remain proportionate instead of inheriting desktop-scale values.
- Rebalanced On This Day around a 16:9 default feature image with no fixed 420px/280px minimum height, preventing the block from dominating laptop and phone viewports.
- Reduced oversized article display typography, hero height, standfirst spacing, entry-content offsets and discovery/endcap spacing.
- Reworked the Minimal and Feature block styles so their names match their actual visual density.
- Tightened recommended new-block card/item spacing without rewriting existing saved block attributes.
- Tightened all 30 pattern files again, including authored top/bottom spacing, block gaps and large fluid display headings.
- Aligned fallback timeline and On This Day counts with the audited global defaults (18 and 8).
- Tightened Gutenberg ServerSideRender preview spacing so editor previews match the front end.
- Added `RESPONSIVE-DENSITY-AUDIT.md` with the cross-viewport density contract.

## 1.4.3

- Rebalanced the entire component scale after real laptop-height usage exposed excessive vertical sizing that width-only responsive checks did not catch.
- Reduced the standard section, page-edge, inter-section and grid spacing scales across standalone Popped blocks.
- Reduced section-heading, On This Day, timeline and magazine-display typography so modules remain editorial without dominating a 13–16 inch laptop viewport.
- Added viewport-height responsive compaction for desktop/laptop screens at 900px and 760px CSS viewport heights.
- Tightened Archive Explorer filters/results, timeline entry spacing, homepage sections and collection rows on short desktop viewports.
- Reduced editor ServerSideRender preview padding so Gutenberg previews match the front-end rhythm instead of appearing artificially oversized.
- Tightened authored vertical padding across all 30 patterns while retaining editable native Gutenberg spacing values.
- Changed the recommended new On This Day title size from Large to Medium; existing saved blocks keep their selected heading size.
- Added `VIEWPORT-SCALE-AUDIT.md` documenting the laptop-height design targets and limits.

## 1.4.2

- Audited the header, logo, search action and navigation overlay as one responsive shell rather than isolated controls.
- Added density-aware header/logo/navigation proportions with a 76px standard header, bounded logo dimensions and 44px+ controls.
- Replaced fixed header height with minimum-height layout, truncated overlong text branding safely, and capped image logos by both height and width.
- Added a quiet `is-scrolled` sticky-header treatment and corrected the WordPress admin-bar offset at the native 782px breakpoint.
- Moved the `below-header` ticker outside the sticky header so enabling it no longer creates an oversized persistent header.
- Rebuilt navigation around a real scrollable panel with dynamic viewport height, a bounded 680px desktop measure and full-width mobile mode.
- Rebalanced top-level navigation typography, submenu spacing, search CTA sizing and touch targets for phone, tablet and desktop.
- Reused the configured site identity inside the navigation panel and replaced font-dependent search/close glyphs with deterministic SVG icons.
- Hardened menu state transitions so rapid reopen/close actions cannot leave the overlay hidden incorrectly.
- Added `HEADER-NAVIGATION-AUDIT.md` with proportional, accessibility and regression contracts.

## 1.4.1

- Fixed oversized blank space above Popped page content caused by theme/template block gaps stacking with Popped section padding.
- Added managed-page detection for Homepage, Timeline, Archive Explorer and Search blocks even when Setup page IDs were not assigned.
- Added explicit zero-margin/zero-padding native page-template mains so block-theme global spacing cannot create a second page-top gap.
- Added `popped-managed-request`, `popped-content-request` and `popped-page-block` layout markers for predictable front-end rhythm without locking editor spacing controls.
- Tightened the standard, compact and spacious section scales and added dedicated page-top, page-bottom and inter-section rhythm tokens.
- Normalized direct sibling Popped blocks so adjacent sections no longer double their full vertical padding.
- Reduced stacked bottom whitespace before the footer and tightened article/discovery transitions.
- Normalized all 30 pattern spacing scales; no pattern section now uses more than 104px of authored vertical padding at its maximum clamp.
- Preserved intentionally roomy feature/hero layouts while removing accidental template-shell whitespace.
- Added `VERTICAL-RHYTHM-AUDIT.md` with the spacing contract and regression checks.

## 1.4.0

- Audited the homepage as one complete editorial journey rather than as isolated blocks.
- Added a new current-first `Editorial lead + sections` homepage composition for new installs, with a dynamically selected lead story and immediate duplicate suppression in Latest Stories, On This Day and Timeline.
- Preserved upgraded sites on the original section-stack renderer until an administrator explicitly opts into the new hierarchy.
- Reordered new-install homepage sections to Latest Stories → On This Day → Featured Collections → Timeline → Explore by Year.
- Added live-homepage empty-state suppression for optional historical and timeline modules while retaining useful editor previews.
- Added homepage-specific responsive typography, spacing, image proportions, section rhythm and equal-weight latest-story cards across phone, tablet and desktop widths.
- Rebuilt both homepage patterns around the same coherent dynamic homepage, using compact editable mastheads instead of competing static hero headlines.
- Added a Components control and Custom Setup choice for the homepage hierarchy.
- Added `HOMEPAGE-EXPERIENCE-AUDIT.md` with the composition rationale, accessibility rules and compatibility model.

## 1.3.3

- Audited global Setup, Design and archive-range defaults for visual quality, readability, safe upgrade behaviour and generic editorial usefulness.
- Added automatic published-post year detection as the recommended range for new installs while preserving existing sites as manual-range configurations.
- Fixed the Components sanitizer so saving component defaults no longer resets the archive range to 1990–1999.
- Preserved stored homepage-section ordering and labels when defaults are merged, preventing upgrades from undoing editor-defined composition.
- Replaced the Britpop-specific new-site homepage label with a generic Timeline label and reordered new homepage sections around a clearer editorial reading flow.
- Reduced new-install Timeline pagination from 24 to 18 and On This Day maximum stories from 12 to 8 for a less crowded default presentation.
- Darkened the Paper accent to `#c83d27` so small accent text meets WCAG AA contrast against the default paper/surface backgrounds.
- Marked recommended Setup/Design options explicitly, clarified motion behaviour, and upgraded the custom-font fallback to a resilient system stack.
- Added `GLOBAL-DEFAULTS-AUDIT.md` with the compatibility model and setting-by-setting rationale.

## 1.3.2

- Audited every Gutenberg control and block-style preset for clearer defaults, ranges, labels and reset behaviour.
- Added versioned recommended insertion defaults through default block variations so new blocks start polished without silently restyling existing published blocks.
- Added visible “Recommended” labels to relevant select controls and per-control reset fallbacks for numeric ranges.
- Added compact spacing preset buttons for card padding, card gaps, image-to-text spacing and internal text rhythm while retaining precise numeric control.
- Added a balanced 1.12 card-title line-height preset, natural-case metadata, semibold card titles and component-specific editorial image proportions for new inserts.
- Changed new News Ticker inserts to static movement with dates hidden by default for maximum readability; motion remains opt-in.
- Made Archive Explorer default to newest-first only for newly inserted blocks; existing inherited archives remain unchanged.
- Replaced the 1–4 column slider with named Feature / Spacious / Balanced / Compact choices and improved On This Day date selection with month names and current-date initialization.
- Added a generic “Timeline” title for new Mini Timeline inserts instead of the previous Britpop-specific placeholder.
- Corrected block-style preset defaults where a named style required a CSS class: Horizontal Timeline, Mini Timeline, Also On This Day and Related Stories now default to the unclassed Editorial baseline.
- Added explicit Apply/Restore Recommended versus Use Inherited Defaults actions in the Styles inspector.
- Added `CONTROL-DEFAULTS-AUDIT.md` documenting the full control-by-control rationale and compatibility model.

## 1.3.1

- Reorganized Popped Inspector controls into Gutenberg Settings and Styles groups: content, story details, layout and exclusions stay in Settings; image, typography, metadata and card appearance move to Styles.
- Added progressive disclosure for excerpt, metadata and crop controls plus clearer help for responsive columns, spacing and destination fallbacks.
- Added section-level resets and an overall Popped appearance reset that intentionally preserves native Gutenberg block-support styles.
- Polished story pickers with decoded titles, selection counts, clear-all controls, stronger focus treatment and narrow-sidebar-safe rows.
- Removed editor-only 10px metadata distortion and direct `.editor-styles-wrapper` targeting for better WordPress 7.1 iframe-editor resilience.
- Improved preview chrome and clarified the distinction between inner Popped card styling and outer Gutenberg Color, Typography, Dimensions and Border controls.

## 1.3.0

- Audited all 15 Popped components and card treatments for standalone and pattern use.
- Added configurable card typography, excerpt scale, metadata styling, card surface/border/radius/padding, internal spacing and image focal-position controls.
- Added Classic 3:2, Cinematic 21:9 and Tall 2:3 image ratios and fixed layout-specific crop precedence so editor selections always win.
- Extended the responsive quality system to raw inserted blocks: safe wrapping, 44px controls, mobile list/grid collapse, preserved excerpts, reduced-motion handling and readable utility text.
- Revalidated all 30 patterns across 280–1920px and at 200% text sizing.

## 1.2.1

- Audited all 30 patterns across phone, tablet, laptop and wide desktop viewports.
- Replaced fixed pattern display typography and large spacing with fluid, bounded values.
- Improved small-text contrast, mobile stacking, story grids, search/filter controls, timelines and horizontal rails.
- Added consistent focus visibility, touch targets, readable measures and text-wrapping safeguards.

## 1.2.0

- Added 15 image-led magazine pattern variants, bringing the Popped library to 30 patterns.
- Added a fluid magazine composition system using `clamp()` sizing, 1280px editorial measure, responsive edge padding and tablet/mobile refinements.
- Added wide, portrait and lead-story image treatments while keeping images controlled by native Popped block settings.
- Kept all pattern structures unlocked and editor-overridable; no template locking or forced immutable design values.
- Tuned magazine compositions for native Columns collapse, Popped 3→2→1 story grids, touch-friendly horizontal rails and proportional timeline layouts.

## 1.1.0

- Rebuilt all 15 patterns as polished native Gutenberg compositions with deliberate hierarchy, spacing, colour and editorial framing.
- Added native Gutenberg background, text, link, gradient, padding, margin, block-gap, border, radius, font-size and line-height controls to every Popped block.
- Added pattern viewport widths, keywords and named editor structure for clearer inserter previews and List View editing.
- Added embedded-component layout normalization so pattern spacing is controlled by editable native wrapper blocks instead of duplicated internal section padding.

## 1.0.1

- Register the Popped pattern category as `popped/patterns`.
- Explicitly expose all 15 plugin patterns to Gutenberg with `source: plugin` and `inserter: true`.
- Register the pattern category before block and pattern registration for WordPress 7.1 compatibility.

## 1.0.0 pattern library

- Added a visible `patterns/` library with 15 editor-insertable Popped patterns.
- Moved the original Editorial Homepage, Article Discovery and Archive Page patterns into dedicated pattern files.
- Added Breaking News Header, Latest Stories Section, Featured Collection Showcase, History Rail, Mini Timeline Feature, On This Day Feature, Timeline Page, Archive Hub, Search Page, Related Stories Grid, Historical Article Endcap and Discovery Article Endcap.
- Registered plugin pattern files explicitly at `init` so the library is not dependent on theme pattern discovery.

## 1.0.0 security hardening

- Removed dynamic post-title HTML assignment from the admin picker; titles are stripped to text, decoded with WordPress's HTML-entity utility, and rendered via `textContent`.
- Stopped rebuilding visitor URLs from the request `Host` header; canonical scheme, host, and port now come from `home_url()` while preserving the current path and query string.

## 1.0.0 final

### Block Editor

- Removed the duplicate custom Inspector preset system so visual variations use the block editor’s Styles panel.
- Limited each block’s Inspector to controls that apply to its component, while keeping unset attributes inherited from component or site defaults.
- Added debounced WordPress data-store searches for post, category and tag choices, with selected labels retained and no fixed 100-record preload.
- Added explicit updating, empty-result and friendly preview-error states for server-rendered previews.

### Components

- Made Also On This Day respect its title, source, count, order, image and metadata settings.
- Made On This Day image visibility, image ratio, heading size, excerpt and independent metadata settings target its hero markup.
- Implemented static, slow and standard ticker movement, pause on hover/focus, and static fallbacks for reduced or disabled motion.
- Kept Magazine only for Archive Explorer, where the first-story hierarchy and grid composition are distinct.
- Reduced registered style choices to variations with component-specific output.

### Collections

- Made Featured Collection inherit the collection’s display style until the block has an explicit native style override.
- Rendered collection featured images and retained configured description, source, count, order and manual story order.
- Replaced collection term and post preloads with debounced, paged REST search.

### Queries

- Centralised manual-source ordering: chosen order uses `post__in`, oldest sorts by publication date ascending, and newest sorts by publication date descending.
- Applied source restrictions, exclusions and bounded counts consistently to On This Day and Also On This Day.
- Preserved bounded queries and cached On This Day/year-count lookups.

### Templates

- Added one native Twenty Twenty-Five header and footer template part to Popped-owned block templates.
- Suppressed plugin-injected shell markup when a block theme owns the visitor shell.
- Repaired only Popped-owned templates while preserving user-created template content.

### Accessibility

- Added keyboard-accessible move-up, move-down and remove actions alongside drag ordering.
- Added labelled REST search results, loading/status announcements, keyboard result navigation and visible focus treatment.
- Kept the ticker usable without animation under Popped Reduced/None motion and `prefers-reduced-motion`.

### Performance

- Replaced large admin record preloads with 250 ms debounced, 20-result REST requests.
- Kept the dependency-free front-end script, shared stylesheet, responsive images and lazy loading.


### Final regression fixes

- Fixed visitor Timeline Oldest/Newest sorting so the submitted visitor choice overrides the block's initial order.
- Made Reduced/None and operating-system reduced-motion ticker fallbacks horizontally scrollable instead of clipping off-screen headlines.
- Made Archive Explorer year/month controls respect the Year filter toggle and preserve active search, taxonomy, date and view context across navigation.
- Removed fixed 80/100-tag caps from visitor Timeline and Archive filters.
- Kept editor instructions inside server-rendered editor previews instead of leaking setup guidance onto public pages.
- Preserved explicit empty-string overrides, including deliberately blank section titles.
- Made Featured Collection native style inheritance distinguish an inherited collection style from an explicit Editorial block override.
- Allowed related-content queries to overfetch safely so automatic selections can fulfil the editor's supported 24-story count after exclusions.
- Split Featured Collection hero-image visibility from story-card image controls; story image shape/fit no longer imply they alter the collection hero.

### Bug fixes

- Separated date, category, tags and author markup so each visibility setting affects only its own field.
- Fixed collection style inheritance and explicit block overrides.
- Fixed post-level Popped Discovery selectors so choices can be searched, selected, saved and reloaded.
- Removed unused preset classes, attributes and legacy style rules that conflicted with current controls.

### Review patch

- Made the configured Popped header, navigation overlay and footer own the shell on managed block-theme requests while keeping the editable native main-template composition.
- Restored global ticker placement for block themes and moved Above Footer rendering immediately before the Popped footer.
- Added a sortable global ticker story picker and preserved chosen order for Mixed ticker sources.
- Ranked automatic related stories by shared taxonomy-term count, then publication-date proximity.
- Made the classic archive fallback render the full Archive Explorer component.
