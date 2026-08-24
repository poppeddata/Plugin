# Popped 1.7.0 design contract

## Product target

Popped should feel like a small set of excellent Gutenberg-native editorial components, not a second page builder. A newly inserted block must be usable and visually finished without configuration. Normal edits must be obvious, and deeper customisation must not make the common workflow noisy.

## Editor contract

1. **Content** chooses what appears.
2. **Story details** chooses which supporting information appears.
3. **Layout & behaviour** changes structural choices only.
4. **Design** is the fast path: density plus Editorial, Compact, Minimal and Cards recipes.
5. **Story images** controls crop, fit and focal position without changing layout geometry unexpectedly.
6. **Type & metadata** controls internal hierarchy, not the whole outer block.
7. **Fine tune** is optional progressive disclosure for surfaces and exact internal spacing.

Native Gutenberg alignment, colour, margin and padding remain available on the outer block. Duplicate whole-block typography, minimum-height and border systems are intentionally not exposed.

## Visitor contract

- Compact enough for ordinary laptop-height viewports.
- No alternating or fragile timeline geometry.
- Horizontal rails are swipeable, keyboard-operable and bounded in width.
- Image ratios and focal positions cannot force a component into an unrelated layout.
- Grids collapse predictably: 4→3→2→1 where appropriate.
- Filters become stacked, full-width controls on narrow screens.
- Touch controls remain at least roughly 44px high where practical.
- Reduced-motion preferences disable ticker animation and transition effects.
- Popped styles only Popped-owned UI; Twenty Twenty-Five remains responsible for ordinary theme content.

## Pattern contract

Only 12 curated patterns ship and are registered for new insertion. Patterns are compositions of functional blocks with minimal hard-coded presentation attributes. The homepage pattern is composed from individually editable blocks rather than the opaque Homepage renderer.

## Compatibility

The normal WordPress Post remains the content model. Publication date remains the historical date. The Timeline tag, existing queries, collections and saved block attributes are preserved.
