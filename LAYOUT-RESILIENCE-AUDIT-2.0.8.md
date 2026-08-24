# Popped 2.0.8 — Block Layout Resilience Audit

This is a static source/package audit of all 15 Popped blocks. It checks intended overflow ownership, shrink-safe grid/flex behavior, long dynamic text, custom labels and translated controls. It is not a substitute for the configured live browser/WordPress CI pass.

| Block | Overflow / wrapping contract | 2.0.8 result |
|---|---|---|
| Popped Homepage | Lead copy and dynamic headings may wrap; no page-level horizontal overflow | Hardened |
| Timeline | Filters wrap responsively; vertical rows use shrink-safe copy columns; horizontal view scrolls intentionally | Hardened |
| Horizontal Timeline | Horizontal rail scrolling is intentional; headings/actions remain shrink-safe | Intentional rail |
| Mini Timeline | Story rail scrolling is intentional; section heading/action cannot force page overflow | Intentional rail |
| On This Day | Long section title, count link and hero title wrap safely; feature collapses to one column responsively | Hardened |
| Also On This Day | Story rail scrolling is intentional; section title remains shrink-safe | Intentional rail |
| Continue the Story | Copy column uses `minmax(0,1fr)` and long context/title text may wrap | Hardened |
| Timeline Previous / Next | Two-column links use shrink-safe columns and long titles wrap; one column on narrow screens | Hardened |
| Related Stories | Uses hardened story-card/list/lead/rail primitives | Hardened |
| News Ticker | Marquee/static ticker owns horizontal movement; label/toggle may wrap rather than widen the page | Intentional ticker |
| Latest Stories | Uses hardened grid/list/lead/rail primitives and section-heading behavior | Hardened |
| Archive Explorer | Filter controls wrap responsively; timeline/list/grid result copy is shrink-safe | Hardened |
| Year Navigator | Grid/inline/list are responsive; Single row fits at most 12 readable years on desktop and uses one-row touch scroll below 600px | Hardened |
| Featured Collection | Image, copy and arrow have explicit grid columns; long names/descriptions wrap safely | Fixed |
| Search | Search/filter controls collapse responsively; long query headings and action labels wrap safely | Hardened |

## Cross-component fixes

- Dynamic section headings and action links receive shrink-safe flex behavior and `overflow-wrap:anywhere`.
- Story-list and timeline copy columns use `minmax(0,1fr)` rather than shrink-unsafe `1fr`.
- Featured Collection rows no longer rely on an implicit third grid column.
- Filter/search actions can wrap when labels or translations are long.
- Homepage lead, discovery links, empty states and result counts tolerate unbroken long text.
- Horizontal scrolling is statically restricted to rails, horizontal timelines, tickers and the narrow-screen fitted Year Navigator fallback.
- The live WordPress smoke test no longer hard-codes an old plugin version; it compares `POPPED_VERSION` with the plugin header.

## Remaining certification

The package release contract is static plus local PHP/JavaScript/CSS syntax validation. The configured WordPress smoke matrix, Plugin Check, and logged-in browser/editor pass must still be green before calling the release production-certified.
