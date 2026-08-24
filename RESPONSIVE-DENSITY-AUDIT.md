# Popped responsive-density audit

Version 1.4.4

## Principle

Responsive correctness is not enough. A module can avoid horizontal overflow and still feel oversized on a real laptop. Popped now treats vertical viewport economy, readable measure and content density as part of the default design contract.

## Standard density contract

- Normal desktop section padding: 34–62px fluid.
- Laptop-height (`<= 900px`) section padding: 29–40px fluid.
- Short laptop-height (`<= 760px`) section padding: 26px.
- Phone section padding: 48px; short phones reduce to 40px.
- Page-leading spacing stays smaller than ordinary section spacing.
- Inter-section flow is always smaller than full section padding so adjacent modules do not create double whitespace.
- Touch targets remain at least 44px even when visual spacing compacts.
- Section headings and card headings use fluid maximums rather than display-scale desktop sizes.
- On This Day uses a 16:9 default feature frame instead of a fixed minimum image height.
- Article hero/title spacing uses a separate, bounded scale so editorial articles remain dramatic without consuming an entire laptop viewport.
- Compact and Spacious modes remain available, but both are viewport-aware.

## Pattern contract

All 30 patterns were re-authored with approximately 15% less vertical top/bottom spacing and tighter large-screen display-heading growth. Minimum mobile heading sizes were retained so the change does not trade density for unreadability.

## Editor contract

Gutenberg ServerSideRender previews use the same tighter section rhythm as the front end. Editor-only chrome keeps its minimum control sizes and is not compressed below usable targets.

## Content limits

A Timeline, Archive Explorer, Latest Stories grid or other block containing many records may naturally extend beyond one viewport. The contract removes wasted vertical scale; it does not hide or artificially squeeze editorial content simply to make every possible result set fit on one screen.

## Compatibility

- Saved block attributes are not rewritten.
- Existing native Gutenberg spacing/color/typography overrides continue to win.
- The global Standard density CSS intentionally changes inherited presentation because the prior baseline was too large.
- New blocks receive slightly tighter recommended item/card/content gaps.
