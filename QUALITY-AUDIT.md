# Popped 1.3.0 quality audit

## Scope

This pass audits all 15 Popped blocks and all 30 registered patterns for responsive readability, card hierarchy, editor customisability and standalone use outside patterns.

## Component audit

| Block | Primary quality checks |
| --- | --- |
| Popped Homepage | Inherits responsive section/card fixes from contained components; no extra fixed-width shell. |
| Timeline | Fluid title scale, readable metadata, selectable crop/focal point, mobile vertical stacking, card spacing controls. |
| Horizontal Timeline | Touch-scrollable rail, bounded mobile card width, selectable crop/focal point, readable titles and metadata. |
| Mini Timeline | Rail cards use the same crop, typography, metadata and spacing controls as grids. |
| On This Day | Hero crop honors selected ratio/focal point, heading scale is fluid, copy measure remains readable, single-column mobile layout. |
| Also On This Day | Rail cards share the full card-design system and mobile-safe width. |
| Continue the Story | Fluid balanced heading, safe wrapping, 44px interaction floor and mobile type scale. |
| Timeline Previous / Next | Readable 13px utility labels, fluid story titles and single-column mobile chronology. |
| Related Stories | Responsive 4→2→1 behavior, full card typography/image/metadata controls and safe lead-card override behavior. |
| News Ticker | Readable utility text, 44px strip height, reduced-motion behavior and horizontal containment. |
| Latest Stories | Responsive card grid, consistent lead-card crop precedence and full card controls. |
| Archive Explorer | Responsive filters, 44px controls, grid/list/timeline/mobile behavior and card controls. |
| Year Navigator | Readable counts, 5→2→1 grid behavior and safe narrow-screen wrapping. |
| Featured Collection | Featured/story image crops, focal positioning, collection/card hierarchy and responsive feature layout. |
| Search | Responsive search controls, readable result cards and preserved excerpts on narrow screens. |

## Editor controls added

Story-producing blocks now expose:

- Card title size, weight and line height.
- Excerpt size and length.
- Metadata visibility, size, tone, case, weight and separator.
- Image ratio: Original, 3:2, 4:3, 16:9, 21:9, 1:1, 4:5 and 2:3.
- Image fit and crop focal position: centre, top, bottom, left or right.
- Image corner style.
- Card surface, border, corner radius and padding.
- Independent gap between cards, image-to-text gap and internal text rhythm.

Native Gutenberg block supports continue to provide block-level background/text/link/gradient, margin/padding/block gap, typography and border controls.

## Validation

- PHP: 46/46 files pass `php -l`.
- JavaScript: 3/3 files pass `node --check`.
- CSS: parsed with no stylesheet errors.
- Patterns: 30/30 unique, unlocked, inserter-visible, and all 15 Popped block types represented.
- Responsive harness: 450 pattern renders across 280–1920px with zero document overflow, clipped text, undersized form controls or phone-width multi-column story grids.
- Text-resize harness: 90 renders at 200% root text size across 320, 375 and 768px with zero document overflow or clipped text.
- Control contract harness confirms 21:9 crop precedence, focal positioning, title/metadata/excerpt styles, card padding/radius/border and mobile one-column collapse.

A final live WordPress 7.1 editor/front-end smoke test is recommended before production deployment because this container does not provide the complete WordPress runtime.
