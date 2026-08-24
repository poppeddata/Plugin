# Header & Navigation Audit

Version 1.4.2 treats the Popped shell as a single proportional system: site identity, search, menu trigger, sticky state, navigation panel and the handoff to page content.

## Proportion contract

- Standard header minimum height: 76px.
- Compact density: 68px; spacious density: 84px.
- Standard logo maximum: 36px high and 220px wide.
- Compact logo maximum: 32px / 200px; spacious: 42px / 240px.
- Phone shell: 64px minimum header, 30px logo maximum, 156px logo width maximum.
- Header controls never fall below 44px.
- Long text site names truncate rather than pushing search/menu actions off-canvas.
- The configured image logo uses `object-fit: contain`; Popped does not crop brand artwork.

## Sticky header contract

The sticky shell stays visually quiet at the top of the page and gains a subtle border/background treatment only after scrolling. The WordPress admin bar uses 32px offset on desktop and 46px through WordPress's 782px mobile breakpoint.

The `below-header` ticker now renders after the header element rather than inside it, so a ticker does not become part of the sticky header's persistent height.

## Navigation contract

- Desktop panel maximum: 680px in Standard density.
- Compact: 640px; spacious: 720px.
- At 760px and below the navigation becomes full width.
- The panel uses dynamic viewport height and its own vertical scrolling, so long menus remain reachable on short devices.
- Top-level links use a bounded 36–56px fluid scale; phone links use a bounded 32–42px scale.
- Submenu links and every explicit button/search action meet a 44px minimum touch target.
- The configured site logo/name is repeated consistently in the navigation panel.
- Search and close controls use inline SVG rather than font-dependent symbols.

## Interaction contract

Opening the navigation:
1. stores the previous focus target;
2. reveals the dialog;
3. marks the menu trigger expanded;
4. moves focus into the dialog.

Closing:
1. marks the dialog hidden to assistive technology;
2. restores page scrolling;
3. marks the trigger collapsed;
4. restores focus;
5. removes the dialog after the transition.

A pending close timer is cancelled before reopening, preventing rapid interactions from hiding a newly reopened panel. Escape closes the dialog and Tab remains trapped within it.

## Responsive audit

The shell was checked structurally at 320, 375, 600, 760, 782, 1024, 1440 and 1920px against:

- brand/action width budgets;
- logo maximum dimensions;
- 44px minimum interaction targets;
- admin-bar offsets;
- navigation panel width rules;
- mobile full-width conversion;
- scroll-safe long menus;
- text-brand overflow behaviour;
- ticker/sticky-header separation.

This is a static/runtime-contract audit in the available environment, not a claim of a logged-in visual WordPress browser session.
