# Vertical Rhythm Audit — Popped 1.4.1

## Goal

Prevent theme, template, pattern and component spacing from stacking into large blank bands while retaining deliberate editorial breathing room.

## Spacing contract

- Page top: `clamp(28px, 4vw, 56px)` at standard density.
- Standard component section: `clamp(52px, 6.5vw, 96px)`.
- Adjacent Popped sections: `clamp(40px, 5vw, 72px)` per side instead of two full section paddings.
- Page bottom: `clamp(44px, 5vw, 72px)`.
- Footer top: `clamp(44px, 5.5vw, 76px)`.
- Phone page top: 28px.
- Phone standard section: 56px.
- Phone page bottom: 52px.

Compact and spacious densities scale the same relationships rather than using unrelated values.

## Theme-shell normalization

Popped's registered page templates now explicitly set the main wrapper margin and padding to zero. Managed block-theme requests also neutralize root/main block gaps before Popped's own page rhythm begins.

A page is treated as managed when it is configured in Setup or directly contains one of these page-level blocks:

- Popped Homepage
- Timeline
- Archive Explorer
- Search

Any page containing a Popped block also receives a content marker used for non-invasive margin normalization.

## Pattern audit

All 30 patterns were checked. Authored vertical padding clamps were normalized so no pattern section exceeds 104px at its maximum value. Pattern-embedded Popped blocks continue to use zero internal section padding so Gutenberg Group padding remains the single source of spacing truth.

## Bottom and section-to-section audit

- Direct adjacent Popped blocks use the inter-section rhythm token instead of stacking full section padding.
- Legacy section-stack homepages use the same rhythm.
- The last homepage section uses the page-bottom token.
- Classic archive/search fallbacks use page-top/page-bottom tokens.
- Article body-to-discovery and discovery-section spacing were reduced to avoid a second oversized transition before the footer.
- Footer top padding was reduced while preserving a clear visual boundary.

## Regression rules

1. Popped page content must not inherit an additional theme top margin.
2. Pattern editor spacing controls remain editable and are not overridden by the page-leading rules.
3. `popped-pattern-embedded` blocks keep zero internal section padding.
4. Direct standalone page blocks get page-leading/trailing rhythm.
5. Adjacent standalone blocks do not stack two full standard section paddings.
6. Existing configured page IDs continue to work; page-level block detection is additive.
