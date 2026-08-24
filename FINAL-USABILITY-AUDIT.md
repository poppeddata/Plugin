# Final Usability Audit

Version 1.5.0 makes content length part of Popped's default design contract.

## Default length contract

- Timeline: 10 stories per page, paginated.
- Horizontal Timeline: 6 stories in a swipeable rail.
- Mini Timeline: 4 stories.
- On This Day: 4 candidates, with one featured story.
- Also On This Day: 4 stories.
- Latest Stories: 5 stories, followed by a View all stories route when more exist.
- Related Stories: 3 stories, one balanced desktop row.
- News Ticker: 5 headlines.
- Archive Explorer: 12 stories per page, paginated.
- Collection index: 5 collections by default; homepage uses 4.
- Year Navigator: new blocks show 12 recent years; the editorial homepage shows 10 and links to the full archive.

## Upgrade behavior

Version 1.5.1 does not infer whether a stored count was an old default or an intentional editorial choice.
Existing stored counts are preserved. The 1.5.x recommendations apply to new installs and newly inserted blocks.

## Editor contract

Count controls use component-specific upper bounds instead of a generic 24/100 range. Controls identify the recommended value and warn that higher values increase section length. Collection indexes and Year Navigator expose their own bounded length controls.

## Visual contract

Large authored pattern mastheads are capped below the previous 4rem+ scale. Dynamic magazine blocks no longer request Large heading presets by default. Existing spacing, responsive density, 44px touch targets, mobile collapse and editor design controls remain intact.

## On This Day image sizing

On This Day now exposes Feature image size presets (Compact, Standard, Large). The control changes the image-to-copy proportion on larger screens while preserving full-width mobile behavior. Compact is the recommended default.
