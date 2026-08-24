# Homepage Experience Audit

> **Historical note:** This document records the 1.4.0 homepage audit. In Popped 2.1.0 the theme owns the site and the old implicit `below-hero` ticker injection was removed. Tickers now appear only where an editor inserts/enables them, except for explicit header/footer injection inside the opt-in legacy Popped shell.

## Scope

Popped 1.4.0 audits the homepage as a complete reader journey: first-screen hierarchy, freshness, section order, duplicate content, spacing rhythm, responsive stacking, empty states, archive discovery and upgrade safety.

## Recommended hierarchy

New installs use **Editorial lead + sections**:

1. **Lead story** — the current lead is selected from the configured Latest Stories source/order.
2. **Latest Stories** — six supporting current stories, with the lead excluded so the first screen does not immediately repeat itself.
3. **On This Day** — historical context after current coverage; suppressed on the live homepage when there is nothing relevant for the date.
4. **Featured Collections** — curated thematic depth when collections exist.
5. **Timeline** — a tactile chronological rail, with the lead excluded and the module suppressed when the source is empty.
6. **Explore by Year** — the broad archive utility at the end of the discovery journey.

The global ticker still honours its configured placement. `below-hero` now means below the actual current lead story in the editorial composition.

## Responsive quality rules

- Lead layout: image/copy split on wide screens, single-column at tablet size.
- Lead headline: fluid `clamp()` scale with balanced wrapping and bounded line length.
- Lead image: stable 3:2 editorial crop; no fixed viewport height.
- Latest Stories: equal-weight three-column support grid, collapsing through two columns to one column.
- Section headings: bounded display scale and balanced wrapping.
- On This Day: split feature collapses before phone widths.
- Year navigator: 5 → 4 → 3 → 2 → 1 columns.
- Touch/keyboard behaviour continues to inherit the global Popped 44px target and focus-visible rules.
- Motion continues to respect the global motion setting and `prefers-reduced-motion`.

## Empty-state policy

The editor still shows useful setup guidance. On the live editorial homepage, optional On This Day and Timeline sections disappear when their sources are empty. This avoids large dead modules while content is still being built.

Latest Stories retains its empty state when there are no posts at all so a new site does not render a completely unexplained blank homepage.

## Pattern changes

Both `Editorial Homepage` and `Popped Homepage — Magazine` now wrap the same global homepage component. Their editable native Gutenberg mastheads are intentionally compact so they do not compete with the dynamic lead-story headline.

The magazine variant changes the masthead and lead treatment, not the content hierarchy. Both homepage patterns explicitly request the editorial composition, so inserting either pattern remains predictable even on an upgraded site that still uses the legacy global section-stack setting.

## Upgrade compatibility

`homepage_composition` defaults to `editorial` for a genuinely new/unconfigured install.

If an existing options record is detected without this setting, Popped resolves it to `sections`. That preserves the previous output and stored section order until the administrator explicitly chooses **Editorial lead + sections** in Popped → Components.

Existing section labels and ordering remain preserved by the settings merge logic.

## Admin control

Popped → Components → Homepage composition now exposes:

- **Editorial lead + sections — Recommended**
- **Section stack — Legacy**

Custom Setup exposes the same choice.

## Audit outcome

The homepage is now current-first rather than archive-first, avoids immediate repeated stories, progressively deepens from current coverage into historical discovery, and remains legible from narrow phones through wide desktop layouts without changing existing sites silently.
