# Popped 1.3.2 control defaults audit

## Compatibility model

Popped now distinguishes two concepts:

- **Inherited defaults** are the existing render-time defaults used by already-published blocks that did not save an override.
- **Recommended insertion defaults** are applied only when a new block is inserted in Gutenberg through a default block variation.

This separation avoids silently changing existing published layouts during the 1.3.2 upgrade. Editors can opt an existing block into the new recommendations with **Apply recommended appearance** and can return to inherited behaviour with **Use inherited defaults**.

## Recommended editorial baseline

| Control | Recommended starting point | Rationale |
| --- | --- | --- |
| Story image shape | Classic 3:2 | Flexible editorial crop with enough height for recognisable imagery. |
| Horizontal / On This Day image | Wide 16:9 | Better proportion for rails and feature treatments. |
| Image fit | Crop to fill | Keeps grids aligned while focal-position controls protect subjects. |
| Image focal position | Centre | Neutral starting point; remains directly editable. |
| Image corners | Use site setting | Respects the site-wide shape system. |
| Card title size | Medium | Readable without overpowering multi-card sections. |
| Horizontal / Mini / Also On This Day / Search title | Small | Prevents narrow-card and list layouts from becoming top-heavy. |
| On This Day title | Large | Maintains a deliberate feature hierarchy. |
| Card title weight | Semibold | Strong scanning without the density of bold. |
| On This Day title weight | Medium | Keeps large feature typography refined. |
| Card title line height | Balanced (1.12) | Safer multiline readability than the previous 1.08 default for new blocks. |
| Excerpt size | Medium | Comfortable body-text hierarchy. |
| Excerpt length | 28 words | Enough context without turning cards into article bodies. |
| Metadata size | Small | Preserves hierarchy while remaining at the audited readable floor. |
| Metadata tone | Muted | Keeps metadata secondary to titles. |
| Metadata case | Natural case | Improves word-shape recognition and avoids shouty all-caps metadata. |
| Metadata weight | Semibold | Keeps 13px metadata legible at low contrast. |
| Metadata separator | Dot | Familiar, compact separation. |
| Card surface | Transparent | Works across themes and pattern backgrounds. |
| Card border | None | Avoids visual noise by default. |
| Card corners | Soft | Subtle polish without a heavily rounded “app card” aesthetic. |
| Card padding | 0 | Preserves editorial edge-to-edge layouts; presets offer 16/24px. |
| Gap between cards | 24px | Balanced desktop rhythm with responsive collapse. |
| Image-to-text gap | 16px | Keeps media connected to its story. |
| Text rhythm | 10px | Compact but readable metadata/title/excerpt spacing. |
| Section spacing | Use site setting | Respects Popped’s site-level density choice. |

## Content and behaviour controls

| Area | Audit result |
| --- | --- |
| Source | Existing component-specific source defaults retained and marked Recommended where applicable. |
| Manual story ordering | Retained; chosen order remains available only when a manual source is active. |
| Story count | Exact numeric input retained with Reset, 1-step increments and Shift acceleration. |
| Columns | Changed from an ambiguous 1–4 slider to named 1 Feature / 2 Spacious / 3 Balanced / 4 Compact choices. |
| Timeline direction | Vertical remains inherited recommendation unless the component configuration says otherwise. |
| Horizontal card width | Medium remains recommended. |
| Archive Explorer | Newly inserted blocks start newest-first; existing inherited archives keep their current behaviour. |
| On This Day date | “Today automatically” remains default. Turning it off initializes the manual date to the current WordPress-local date and uses month names. |
| Year Navigator | Existing site-configured year range remains the recommendation; range inputs now have exact numeric fields and reset values. |
| News Ticker movement | New inserts start Static for readability. Slow and Standard remain available. |
| News Ticker dates | New inserts hide dates to reduce ticker clutter. Existing tickers are unchanged. |
| Mini Timeline title | New inserts use the generic “Timeline” instead of “Britpop”. |
| Filters | Existing Archive/Search defaults retained because they match the component’s discovery purpose. |

## Spacing presets

Precise sliders remain available. Quick buttons provide common values without introducing a whole-card style preset system:

- Card padding: Edge 0 / Comfortable 16 / Spacious 24
- Gap between cards: Compact 16 / Balanced 24 / Airy 32
- Image-to-text gap: Tight 12 / Balanced 16 / Airy 24
- Text rhythm: Tight 8 / Balanced 10 / Airy 16

## Block-style preset audit

Four components previously marked a class-dependent style as the default even though WordPress does not add the class for a default style. Their default preset is now the unclassed **Editorial** baseline:

- Horizontal Timeline
- Mini Timeline
- Also On This Day
- Related Stories

Alternative Filmstrip, Simple, Compact, Cards and Minimal presets remain available.

## WordPress 7.1 alignment

The implementation uses:

- default block variations for new-instance attributes;
- Settings / Styles inspector groups;
- SelectControl for small discrete choice sets with strong defaults;
- RangeControl reset fallbacks and exact numeric input where a range is genuinely useful.

No responsive front-end rules, pattern markup, published block attributes or existing inherited render defaults are migrated automatically in this release.
