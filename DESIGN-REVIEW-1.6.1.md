# Popped 1.6.1 design and Gutenberg audit

## Scope

This pass audits the complete supplied plugin against the 1.6.1 design brief:

- 15 registered dynamic Popped blocks
- 8 native block-theme templates
- 5 classic/legacy PHP fallback templates
- 30 registered patterns, of which 12 remain visible in the inserter
- front-end, editor and admin presentation layers

The data/query model, REST behaviour, block names and existing saved attributes are unchanged.

## Major design problems found

1. The strongest 1.6 visual rules lived in a second `polish.css` cascade while older conflicting rules remained in `popped.css`.
2. On block themes, native header/footer template parts were rendered in templates and then suppressed while Popped injected a second site shell. That fought Twenty Twenty-Five.
3. The editor still overlaid every server-rendered block with a Popped “Live preview” badge.
4. Several block style catalogues treated a stylised Editorial variant as the implicit default, including the lead-card asymmetry used by story grids.
5. Exact card spacing controls were always exposed, despite four values normally changing together.
6. Search pagination was emitted inside the results grid and the search field forcibly autofocuses.
7. The native single template imposed oversized Popped-specific article typography instead of letting the block theme compose the article.
8. The news ticker lacked direction/separator controls and required stronger optical vertical centring.
9. Existing block-theme typography defaults did not provide a true Global Styles inheritance path for new installs.

## Architectural changes

- Current component presentation is consolidated into `assets/css/popped.css`.
- `assets/css/polish.css` remains only as a non-enqueued compatibility placeholder.
- Where the canonical component layer owns a property, conflicting earlier root-level property ownership was removed rather than adding another runtime stylesheet.
- All audited front-end selectors remain scoped to Popped-owned classes (or `:root` tokens); no unscoped normal Twenty Twenty-Five block selectors are introduced.
- Block themes retain their native header/footer template parts and site identity. The Popped shell remains the classic/fallback path.
- The active Popped typography/density/shape/motion classes are also placed on server-rendered block wrappers so editor previews and front-end blocks resolve the same component tokens.

## Native Gutenberg controls

Popped dynamic blocks expose native Gutenberg support for:

- wide/full alignment and anchor
- text/background/link colours and gradients
- margin, padding and block gap
- font size and line height
- font family, style, weight, letter spacing and text transform
- border colour, width, style and radius
- minimum height

These controls apply at the normal block Inspector/Styles level rather than on a separate Popped admin page.

## Specialist Popped controls retained

Specialist controls remain only where native block supports cannot express the dynamic/query behaviour cleanly:

- query source, count, order, filters, exclusions and manual selections
- timeline orientation, grouping and navigation behaviour
- story image visibility, queried-image aspect ratio, crop/fit and focal position
- On This Day feature sizing
- story-title/excerpt hierarchy and metadata treatment inside repeated queried cards
- restrained card surface/border/radius recipes
- ticker movement speed, direction, separator, label/date behaviour and pause
- named collection selection

Card spacing now starts with Compact/Balanced/Airy recipes. Exact card padding, media gap, content gap and item gap are hidden until explicitly requested.

## Visible pattern library

The 12 inserter-visible patterns are:

1. Popped Homepage (`editorial-homepage`)
2. Popped Timeline Page (`timeline-page`)
3. Popped Archive Hub (`archive-hub`)
4. Popped Search Page (`search-page`)
5. Popped On This Day (`on-this-day-feature`)
6. Popped Latest Stories (`latest-stories-section`)
7. Popped History Rail (`history-rail`)
8. Popped Collections (`featured-collection-showcase`)
9. Popped News Ticker (`breaking-news-header`)
10. Popped Article Discovery (`article-discovery`)
11. Popped Historical Endcap (`historical-article-endcap`)
12. Popped Related Stories (`related-stories-grid`)

The other 18 pattern registrations remain available for saved-content compatibility but are hidden from new insertion.

## Responsive changes

- Fluid spacing tokens are compacted further on laptop-height viewports.
- 4/3-column story/archive grids reduce to two columns at 1024px and one column on phones.
- Lead-card asymmetry is now an explicit Editorial/Feature choice and is neutralised at smaller widths.
- On This Day uses bounded portrait/square media on desktop, then becomes a deliberate single-column composition on tablet/mobile.
- Vertical timeline entries use a compact one-sided chronology and switch to a stacked mobile treatment.
- Horizontal timelines remain swipeable rails with deliberate mobile card widths.
- Filters reorganise at tablet and phone widths rather than simply shrinking.
- Search controls move from a wide grid to two-column and then single-column compositions.
- Collections and year navigation collapse deliberately.
- Exact 390px edge spacing is included, with reduced navigation and timeline rail widths.
- Reduced-motion behaviour remains enforced for ticker/scroll animation.

## Compatibility compromises

- Existing saved typography choices continue to render exactly as stored. Only genuinely new installs default to Theme / Global Styles; sparse pre-1.6.1 option records retain the historical Editorial default.
- The classic-theme fallback shell is retained, including its Popped logo/navigation settings. On Twenty Twenty-Five these site-identity controls are correctly delegated to the Site Editor.
- Legacy patterns and historical CSS contracts that are still needed by saved content remain registered/retained; current component property ownership is consolidated where safe instead of deleting compatibility selectors wholesale.
- Existing block names, saved attributes, storage/query architecture and content model are unchanged.

## Automated checks performed

- `php -l` passes for all 46 PHP files.
- `node --check` passes for `admin.js`, `blocks.js` and `popped.js`.
- `tinycss2` reports no top-level stylesheet parse errors.
- Static selector audit finds no non-Popped front-end selectors in `popped.css`.
- No PHP/JS runtime references to the old `popped-polish` handle or `polish.css` remain.

## Still requires live-browser verification

A real WordPress + Twenty Twenty-Five runtime is still required to verify:

- exact Site Editor/template precedence on the target WordPress release
- rendered content at 1440, 1280, 1024, 768 and 390px with representative landscape/square/portrait media
- browser console output and PHP runtime warnings under real queries
- keyboard/focus behaviour through native theme navigation plus Popped filters/rails
- visual interaction between user-selected Global Styles and each native block support
- long real-world titles, translated strings and unusual featured-image crops
