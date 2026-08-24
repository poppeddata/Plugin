=== Popped ===
Contributors: popped
Tags: timeline, editorial, archive, on this day, block theme
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A refined, image-led editorial archive and historical timeline that coexists with your WordPress theme.

== Description ==

Popped turns normal WordPress posts into a polished historical archive. Publication dates remain the historical/event dates, and posts tagged Timeline make up the primary timeline. It does not add custom post types or duplicate editorial data.

Highlights:

* Safe Quick Setup and a short Custom Setup flow
* Six focused admin tasks: Setup, Design, Components, Collections, Templates / Display and Advanced
* Drag-and-drop homepage section manager and named reusable collections
* A composed editorial homepage pattern made from real, independently editable Popped blocks
* Theme-safe page rhythm that prevents stacked top/bottom gaps around Popped page blocks and patterns
* A compact responsive component system tuned for phones, tablets, ordinary laptops and large desktops
* Vertical and genuine horizontal timeline views
* Searchable human category, tag and post selectors—no IDs to type
* Live server-rendered previews with a simplified Content / Layout / Design editing model
* Zero-CSS native controls for story and utility blocks, including exact type, colour, alignment and mobile overrides
* Fast block-toolbar alignment for the controls used most often
* A deliberately focused 17-pattern library for reusable editorial sections, archive exploration and article discovery
* Compact search, year, category, tag, month, order and view filters
* On This Day hero and same-date discovery
* Mini Timeline, Latest Stories, configurable Year Navigator and Featured Collection blocks
* Continue the Story, chronological navigation and Related Stories
* Automatic, manual or mixed news ticker
* Archive Explorer and taxonomy-aware search
* Full-screen, keyboard-accessible navigation with custom SVG support
* Preset-first design controls, semantic colours and WOFF2/WOFF/TTF/OTF custom fonts
* Theme-owned homepage, single, archive, search and 404 templates; Popped owns blocks, not the site
* Responsive, reduced-motion-aware front end with no jQuery dependency

== Installation ==

1. Use your preferred WordPress theme.
2. Upload the `popped.zip` file through Plugins > Add New > Upload Plugin.
3. Activate Popped.
4. Open Popped > Setup and select Quick Setup.
5. Add or edit normal Posts. Set each post's publication date to its historical date.
6. Add the Timeline tag to posts that should appear in the main timeline.
7. Add featured images, categories and subject tags normally.

Quick Setup is idempotent. It reuses Popped-owned timeline, archive and search pages, repairs missing required blocks without replacing other content, and uses a safe fallback slug if an unrelated page already has the preferred name. It never creates, replaces or selects your homepage.

== Frequently Asked Questions ==

= Does Popped create an event content type? =

No. Every story or event is a normal WordPress Post.

= Where is the historical date stored? =

In the normal WordPress publication date. Popped uses it for chronology, On This Day, year/month archives and previous/next navigation.

= How does a post enter the timeline? =

Give it the Timeline tag. The tag slug can be changed under Popped > Components.

= Can I keep my theme templates? =

Yes. Popped 2.1 leaves site templates to the active theme by default. The optional legacy classic-theme shell is off unless you explicitly enable it under Popped > Templates / Display.

= Does Popped control my homepage? =

No. Popped does not register a front-page template, create a homepage, or change WordPress Reading Settings. Build and select your homepage normally in WordPress. The Popped Homepage block and homepage pattern remain optional building blocks.

= Does uninstall delete content? =

No. Popped preserves posts, archive pages, terms and settings. Uninstall only removes legacy Popped-owned site-template overrides and releases old plugin page-template assignments so WordPress cannot be left pointing at missing Popped templates.

== Accessibility ==

Popped includes visible focus states, semantic landmarks and headings, keyboard-operable rails, a focus-managed full-screen menu, a classic-template skip link, touch-friendly controls, alt text inherited from the Media Library, explicit ticker Pause / Resume controls, an explicit Reduced/None motion setting and `prefers-reduced-motion` support.

== Changelog ==

= 2.1.0 =
* Made theme ownership the default: Popped no longer creates or repairs site-level Single, Archive, Search, 404 or Front Page templates.
* Retires only legacy database templates carrying Popped's ownership marker and releases old Popped page-template assignments; uninstall performs the same targeted presentation cleanup.
* Made the legacy classic-theme shell, automatic article-discovery append and site-wide taxonomy search enrichment opt-in.
* Setup now creates only optional Timeline, Archive and Search pages plus the Timeline tag; it never changes the homepage, navigation, site name or page templates.
* Migrated all 15 blocks to canonical per-block `block.json` metadata with selective view-script loading.
* Replaced raw block URL, colour and font fields with WordPress link controls, theme/Global Styles colour palettes and theme-exposed font-family choices.
* Added configurable story and section heading levels, site date-format support, bounded taxonomy filters, RTL-aware rails with logical controls and safer pointer-drag behavior.
* Design tokens now travel with Popped block styles, including inside Site Editor templates and Query contexts.
* News Ticker placement is explicit: use the block/Homepage section; automatic header/footer injection applies only to the opt-in legacy shell.
* Removed Random ordering from the editor/admin UI and deterministic legacy fallback avoids `ORDER BY RAND()` on large archives.
* Versioned On This Day and year-count caches so published-content changes invalidate persistent object-cache results.
* Added Playwright E2E coverage for all-block registration/insertion, editor persistence, long-content overflow, theme ownership/design-token delivery and horizontal-timeline accessibility.
* Expanded live WordPress smoke tests and release-contract checks around theme ownership, selective assets, block metadata and upgrade cleanup.

= 2.0.8 =
* Hardened all Popped block layouts against long titles, labels, translated controls and narrow parent containers.
* Capped Single row (fit) at 12 years so the desktop presentation remains physically readable; longer ranges expose the full-archive link.
* Made section headings/actions, list cards, vertical timelines, discovery cards and collection rows shrink safely without horizontal page overflow.
* Fixed Featured Collections rows so image, copy and arrow use explicit grid columns instead of an implicit overflow-prone column.
* Allowed filter/search action labels and ticker controls to wrap safely when translations or custom labels are long.
* Preserved intentional horizontal scrolling only for rail/timeline/ticker experiences and the narrow-screen Year Navigator fallback.
* Added a release contract for layout-resilience selectors and Featured Collection row markup.
* Made the live WordPress smoke test version-safe by comparing the runtime constant with the plugin header instead of a stale hard-coded release number.

= 2.0.7 =
* Changed Year Navigator Single row from desktop horizontal scrolling to an equal-width fit layout.
* Keeps narrow-screen touch scrolling so year labels remain usable on phones.
* Updated regression coverage for the desktop single-row fit contract.

= 2.0.6 =
* Added a built-in single-row scrolling Year Navigator presentation; no Additional CSS required.
* Clarified wrapped inline presentation and added grid-column and newest/oldest ordering controls.
* Expanded Year Navigator editor bounds to the supported 1000–3000 range and 100 rendered years.
* Added an explicit limit-years toggle so the editor accurately reflects unlimited existing navigators.
* Promoted story/section alignment into the primary Design panel for easier discovery.

= 2.0.5 =

* Removes Popped ownership of the WordPress front page: no `front-page` block template is registered or created.
* Stops Setup from creating, repairing or selecting a homepage and leaves `show_on_front` / `page_on_front` untouched.
* Retires only legacy database `front-page` templates explicitly marked as Popped-managed so existing installs stop being overridden.
* Prevents classic-theme fallback templates, shell assets and Popped header/footer ownership from claiming the site front page.
* Keeps the Popped Homepage block and editorial homepage pattern available as optional components you can insert into any page.
* Adds regression coverage proving homepage ownership remains with WordPress/the active theme.

= 2.0.4 =

* Fixes Custom Setup so the recommended Theme / Global Styles typography choice is preserved instead of silently falling back to Editorial.
* Synchronizes the Homepage `composition` attribute between PHP and Gutenberg JavaScript registration.
* Makes Setup completion conditional on successful tag, page, homepage and navigation work, and rejects trashed managed pages during repair.
* Moves plugin textdomain loading to `init` priority 0 for current WordPress translation timing.
* Internationalizes block catalogue metadata, admin choices, homepage section labels and native 404 template strings that previously bypassed gettext.
* Bounds visitor-facing year controls to useful published years with a hard 100-control ceiling instead of generating potentially thousands of links or options.
* Expands the release contract with client/server schema, setup-state, page-status, translation, year-bound and package-hygiene regressions.
* Adds a live WordPress smoke suite and CI jobs for WordPress 6.7.7 / PHP 7.4 and WordPress 7.1 / PHP 8.3–8.4, plus the official WordPress Plugin Check action.
* Keeps saved posts, block names and existing 2.0.x block attributes data-compatible.

= 2.0.3 =

* Restores the intended Timeline Previous / Next utility alignment and gap defaults by removing an unreachable duplicate switch case.
* Registers JavaScript translations for the Gutenberg editor bundle so `wp.i18n` strings can load through WordPress translation infrastructure.
* Adds a reproducible release contract checker and CI workflow for PHP/JavaScript syntax, release metadata, block defaults and editor translation wiring.
* Keeps the patch release data-compatible: no block names, attributes, stored content, queries or front-end markup are migrated.

= 2.0.2 =

* Makes the news ticker gapless across short headline sets and wide viewports by measuring the source group and creating only the visual clones required for continuous coverage.
* Keeps ticker clones out of the accessibility and keyboard focus order, and removes clones entirely for static or reduced-motion presentations.
* Adds a persistent keyboard-accessible Pause / Resume control for moving tickers while preserving the optional pause-on-hover/focus setting.
* Fixes the ticker pause setting so disabling pause-on-hover/focus now has a real front-end effect.
* Adds a classic-template skip link, translates the remaining admin JavaScript UI strings, and aligns PHP/JavaScript block support declarations.
* Registers Popped front-end assets as block assets and limits explicit global enqueues to Popped-managed shells and automatic article discovery.
* Synchronises the plugin and WordPress.org release metadata at version 2.0.2.

= 2.0.0 =

* Completes the zero-CSS editing model across story and utility blocks.
* Adds native alignment, exact type sizing, semantic colours, spacing and mobile overrides to utility components.
* Adds focused Timeline Explorer and Year Explorer patterns and preserves existing saved block markup.

= 1.8.0 =
* Adds zero-CSS exact styling controls for story-driven blocks: alignment, per-element font family, exact sizes and colours.
* Adds optional mobile typography and story-column overrides while retaining responsive defaults.
* Keeps Popped design recipes and presets as the default editing path.


= 1.7.0 =

* Rebuilt the front-end presentation around one compact component system instead of successive responsive correction layers.
* Simplified block editing into clear content, story detail, layout and design decisions; removed redundant whole-block typography, min-height and border controls that could fight component styling.
* Added four practical design starting points: Editorial, Compact, Minimal and Cards, with density promoted to the main Design panel and exact spacing kept behind Fine tune.
* Rebuilt story grids, lists, timelines, horizontal rails, On This Day, filters, search, archive controls, collections, year navigation and article discovery around bounded responsive rules.
* Made the recommended homepage a composition of individually editable Gutenberg blocks instead of one opaque homepage block.
* Registered only the 12 curated patterns for new insertion and removed fixed micro-spacing from those patterns so block defaults stay authoritative.
* Kept normal posts, queries, collections, historical-date behaviour and existing saved block attributes intact.

= 1.6.1 =

* Let Twenty Twenty-Five and other block themes own their native header/footer template parts and site identity.
* Consolidated current component presentation into `popped.css`; the former `polish.css` runtime layer is no longer enqueued.
* Added Theme / Global Styles as the typography default for new installs while preserving existing saved presets.
* Expanded native Gutenberg typography and minimum-height support and simplified exact card-spacing controls behind progressive disclosure.
* Made calm story grids the true default; lead asymmetry is now an explicit Editorial/Feature style.
* Added ticker direction and separator controls, improved vertical centring, and kept reduced-motion behaviour.
* Rebalanced On This Day, timelines, filters, search, archive navigation, discovery modules and classic fallback article proportions.
* Removed editor “Live preview” chrome so server-rendered content remains visually dominant.

= 1.6.0 =

* Reworked the visual system around Twenty Twenty-Five instead of globally restyling ordinary theme content.
* Added a coherent front-end polish layer with tighter laptop-scale rhythm, calmer surfaces, balanced story grids and more robust On This Day image proportions.
* Rebuilt the Popped admin and Gutenberg editor presentation with quieter native-feeling controls and less decorative framing.
* Added four one-click appearance starting points: Editorial, Compact, Feature and Soft cards.
* Curated the inserter to 12 useful page, section and article patterns while retaining older patterns for compatibility.
* Re-authored the curated patterns to avoid hard-coded decorative colours and oversized demo content.
* Scoped Popped typography and reduced-motion rules so activation does not take visual ownership of unrelated Twenty Twenty-Five content.

= 1.4.2 =

* Rebalanced the complete header/logo/navigation system across compact, standard and spacious densities.
* Constrained uploaded logos by both height and width so unusual artwork cannot distort the header or collide with actions.
* Replaced fixed header heights with minimum-height rhythm that remains safe under text zoom and long site names.
* Added a subtle scrolled sticky-header state and corrected the WordPress admin-bar offset through the 782px breakpoint.
* Moved the below-header ticker outside the sticky header so headline strips do not become part of the persistent header height.
* Rebuilt the navigation overlay as a scrollable, dynamic-viewport-height panel with bounded desktop width and full-width mobile presentation.
* Tightened navigation typography, submenu rhythm, touch targets, focus behaviour and mobile proportions.
* Replaced font-dependent search/close glyphs with consistent inline SVG icons.
* Fixed a rapid reopen/close race that could hide the navigation after it had been reopened.
* Added `HEADER-NAVIGATION-AUDIT.md` documenting the responsive shell contract.

= 1.3.3 =

* Audit Setup, Design and archive-range defaults for new installations while preserving existing saved choices.
* Replace the generic Britpop homepage placeholder with Timeline for new installs and preserve existing custom/homepage labels on upgrades.
* Add automatic archive-year detection from published posts as the recommended new-install mode; existing sites keep their saved manual range.
* Fix Components-page saves so they can no longer reset the historical year range to 1990–1999.
* Preserve administrator-defined homepage section ordering when settings are merged with future defaults.
* Tighten new-install editorial load with 18 Timeline stories per page and 8 On This Day stories.
* Improve the Paper colour preset with a darker accessible accent and align the admin accent with readable small-text contrast.
* Mark recommended Setup/Design choices clearly and use a resilient system fallback stack for custom fonts.

= 1.3.2 =

* Audit Gutenberg control defaults and add recommended new-block insertion values without restyling existing blocks.
* Improve card image proportions, typography, metadata defaults, spacing controls and archive ordering for new inserts.
* Replace ambiguous column sliders with named layout choices and correct class-dependent default block styles.

= 1.3.1 =

* Reorganize custom block controls around Gutenberg’s Settings and Styles tabs so content/behaviour and appearance are no longer mixed together.
* Split story detail visibility from story typography and metadata styling, with progressive disclosure when excerpts or metadata are disabled.
* Add clear Popped appearance status, section resets and a safe all-Popped-appearance reset that leaves native Gutenberg styles untouched.
* Improve post pickers with decoded titles, selected-story counts, clear-all action, stronger keyboard focus and more readable narrow-sidebar rows.
* Remove editor-only 10px date styling and brittle direct editor-wrapper selectors so live previews more closely match the front end in WordPress 7.1.
* Clarify responsive column controls, section spacing, destination fallbacks, image crop behaviour and the boundary between Popped card styles and native Gutenberg block styles.

= 1.3.0 =

* Audit all 15 Popped components and their card treatments against the pattern-library responsive quality standard.
* Add editor controls for card title weight/line-height, excerpt size, metadata size/tone/case/weight/separators, card surfaces/borders/corners/padding, image-to-text rhythm and internal text rhythm.
* Expand image cropping with 3:2, 21:9 and 2:3 ratios plus focal-position controls, and make selected crops override lead/list layout defaults consistently.
* Apply standalone-block mobile stacking, readable utility-text sizing, 44px controls, focus visibility and safe wrapping outside pattern embeds.
* Preserve excerpts on phones and collapse explicit multi-column story/archive grids to one column at narrow widths.

= 1.2.1 =

* Audit all 30 patterns across phone, tablet, laptop and wide desktop viewports.
* Replace fixed pattern display typography and large spacing with fluid, bounded values.
* Improve small-text contrast and establish a readable utility-text floor.
* Harden mobile stacking, story grids, search/filter controls, timelines and horizontal rails against overflow.
* Add consistent focus visibility, touch targets, readable measures and text-wrapping safeguards across the pattern library.

= 1.2.0 =

* Add 15 image-led magazine pattern variants, bringing the Popped library to 30 patterns.
* Add a responsive magazine composition system with fluid typography, spacing and editorial proportions.
* Keep all magazine patterns unlocked and editable with native Gutenberg structure and block controls.
* Tune story grids, portrait/wide image treatments, timelines and horizontal rails for desktop, tablet and mobile layouts.

= 1.1.0 =

* Redesign all 15 Popped patterns as polished native Gutenberg compositions.
* Add editable spacing, padding, colour, gradient, border, radius, font-size and line-height support to every Popped block.
* Add inserter preview widths, search keywords and named editor structure to the pattern library.
* Normalize embedded Popped components so pattern spacing is controlled by the surrounding native blocks.

= 1.0.1 =

* Register the Popped pattern library in a namespaced Gutenberg category and explicitly expose plugin patterns in the inserter.

= 1.0.0 =

* Initial 1.0.0 release. See CHANGELOG.md for verified implementation details.
