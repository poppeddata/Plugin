# Popped viewport-scale audit

Version 1.4.3

## Why this pass exists

Previous responsive QA concentrated on horizontal overflow, clipping, mobile collapse and text zoom. That was insufficient: a layout can pass those checks and still consume too much of a laptop-height viewport.

The 1.4.3 pass treats vertical viewport economy as a first-class requirement.

## Targets

- Standard standalone section padding: 40–72px on normal desktop viewports.
- Laptop-height (`<= 900px`) standard section padding: 34–46px.
- Short laptop-height (`<= 760px`) standard section padding: 30px.
- Standard page-leading spacing: 20–40px, reduced further on short viewports.
- Section headings: capped at 58px in the normal system and reduced further by viewport height.
- On This Day: medium is the recommended new-block title size; the feature remains image-led without using display-scale typography by default.
- Story/archive row gaps and timeline entry spacing are reduced on short viewports.
- Magazine patterns retain strong hierarchy while using a smaller section/display scale.
- Gutenberg ServerSideRender previews use the same compact visual rhythm instead of adding a second oversized preview rhythm.

## Important limit

A block that deliberately renders many stories, years, filters or timeline entries can and should extend beyond one viewport. The goal is to eliminate wasted scale and make a representative module legible at once, not to compress arbitrary amounts of editorial content into one screen.

## Compatibility

- Existing block attributes are not rewritten.
- Existing manually selected heading sizes, image ratios, card spacing and native Gutenberg spacing controls remain editable.
- The CSS scale change intentionally affects inherited/default presentation globally because the previous inherited scale was too large.
