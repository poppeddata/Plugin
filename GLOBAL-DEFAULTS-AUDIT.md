# Popped global defaults audit — 1.3.3

## Goal

Make a fresh Popped installation look coherent, readable and generic without silently changing an established site's saved design or editorial structure.

## Design defaults

| Setting | 1.3.3 recommendation | Rationale |
| --- | --- | --- |
| Typography | Editorial | Strong hierarchy for image-led archive/storytelling work while body copy stays on the system sans stack. |
| Density | Standard | Balanced section rhythm across phone, tablet and desktop. |
| Shape | Soft | 4px treatment adds polish without making editorial cards look app-like. |
| Motion | Standard | Keeps quiet transitions and rails; `prefers-reduced-motion` remains authoritative and Reduced/None remain explicit options. |
| Colour | Paper | Warm neutral remains the strongest generic editorial base. Accent is darkened to `#c83d27` for readable small accent text. |
| Sticky header | On | Useful for long timelines/archives while retaining the compact 76px shell and mobile/admin-bar handling. |
| Custom font fallback | System UI stack | Avoids Arial-only fallback and keeps text metrics/readability resilient while a custom font loads. |

Default preset contrast against the default surface/background now keeps muted and accent text at or above WCAG AA for normal-sized text.

## Setup defaults

| Setting | 1.3.3 recommendation | Change |
| --- | --- | --- |
| Setup path | Quick Setup | Kept as the primary safe path. |
| Timeline direction | Vertical | Kept; easiest to scan and most robust responsively. |
| Homepage lead | On This Day | Kept as the distinctive archive-first lead. |
| Homepage flow | On This Day → Latest Stories → Timeline → Featured Collections → Explore by Year | Reordered for a clearer current-to-context-to-deep-browse reading path on new installs. |
| Mini Timeline label | Timeline | Replaces the Britpop-specific placeholder on new installs. |
| Timeline page size | 18 | Reduced from 24 to avoid an overlong first page while keeping meaningful depth. |
| On This Day maximum | 8 | Reduced from 12 to keep the hero/supporting-story section focused. |
| Archive year range | Automatic | New installs follow the oldest/newest published post instead of assuming 1990–1999. |
| Popped visitor experience | On | Kept because the plugin is explicitly designed to provide the integrated editorial shell after Setup. |

## Upgrade compatibility

- Existing saved Design choices are not overwritten.
- Existing Paper colour arrays remain unchanged until the administrator explicitly selects Paper again.
- Existing sites without `year_range_mode` are treated as Manual so their saved first/last years continue to render.
- Existing homepage section order is now preserved when settings are merged.
- Existing homepage labels are preserved; a site that deliberately has “Britpop” keeps it.
- New sections introduced by future versions are appended after the stored order instead of reshuffling the homepage.

## Correctness fixes found during the audit

1. Saving the Components page previously ran the archive-range sanitizer even though Components does not submit year fields. That could reset the range to 1990–1999. Components saves no longer touch archive years.
2. Recursive default merging rebuilt `homepage_sections` in default-key order, which could undo an administrator's drag-and-drop homepage order. Stored order now wins.
3. Hard-coded 1990–1999 year controls could make archive filters incomplete on a fresh site. Automatic mode now follows published post dates.
4. The original Paper accent (`#e04b2f`) was below 4.5:1 against Paper/white when used as small text. The new accent (`#c83d27`) clears that threshold.

## Validation

- PHP syntax: all plugin PHP files pass `php -l`.
- JavaScript syntax: all plugin JavaScript files pass `node --check`.
- Compatibility harness confirms legacy year mode, homepage ordering/labels, Components-save range preservation, and automatic year detection.
- Default Paper/admin accent and muted text contrast were checked against their default backgrounds/surfaces.
