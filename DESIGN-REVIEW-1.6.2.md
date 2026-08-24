# Popped 1.6.2 compactness pass

This follow-up pass responds to the visual review that Popped blocks were still too large.

## What was actually causing the scale problem

The 1.6.1 canonical component layer had reduced many global values, but the block-specific `.popped-local-density-standard` class still applied 40–72px section padding. The explicit `popped-heading-medium` rules also overrode smaller generic component headings. Homepage composition rules and magazine-pattern spacing retained a larger presentation scale as well.

## Changes

- Standard local section padding: 40–72px → 26–42px.
- Compact local section padding: 30–50px → 20–30px.
- Spacious local section padding: 52–92px → 36–54px.
- Standard local gaps: 18–36px → 12–20px.
- Default card media/content gaps: 18/12px → 12/8px.
- Medium story-title maximum: 1.95rem → 1.5rem.
- Medium vertical-timeline title maximum: 2.75rem → 1.9rem.
- Medium On This Day title maximum: 3.4rem → 2.4rem.
- Reduced default On This Day media footprint and desktop portrait/square caps.
- Reduced timeline entry spacing and horizontal card width.
- Reduced homepage lead, section and mobile typography/spacing.
- Reduced search, discovery, collection/year and magazine-pattern scale.
- Curated pattern card-gap values and newly inserted block spacing defaults are tighter.
- Editor previews use the same smaller visual rhythm.
- Interactive controls retain their accessibility minimums.

No block names, saved attributes, query architecture or storage behaviour were changed.
