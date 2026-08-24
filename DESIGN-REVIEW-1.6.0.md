# Popped 1.6.0 design review

## Product goal

Popped should feel like a high-quality extension of Gutenberg and Twenty Twenty-Five: useful editorial functionality that looks deliberate immediately, but remains easy to alter with standard block controls.

## Problems found in the incoming build

1. **Theme ownership was too broad.** Activating the plugin applied body-level typography, colours, links, focus and motion rules beyond Popped-owned UI. This made Popped compete with Twenty Twenty-Five rather than extend it.
2. **The visual system had become patch-driven.** Repeated responsive and density corrections produced many overlapping rules, making the final hierarchy less coherent than the amount of CSS suggested.
3. **Patterns were over-supplied and under-curated.** The inserter exposed base and magazine variants of similar ideas, while many patterns carried their own promotional copy, colour decisions and spacing.
4. **Editor controls made simple changes feel advanced.** The plugin already had strong granular controls, but users had to make too many individual decisions to reach a polished result.
5. **Editor/admin chrome looked more custom than WordPress.** Heavy connected borders, high-contrast hover inversions and the large live-preview label made the interface feel bolted on.
6. **Image choices could destabilise layouts.** On This Day and other image-led modules needed stronger proportional limits so changing crop/ratio did not make the whole block suddenly dominate a laptop viewport.

## 1.6.0 design decisions

- Ordinary theme content is left to Twenty Twenty-Five and Global Styles. Popped styling is scoped to Popped blocks and managed templates.
- A final `polish.css` coherence layer establishes one responsive rhythm without deleting compatibility CSS from older versions.
- Story grids are calmer by default; lead-card asymmetry is reserved for explicit editorial/feature treatments.
- On This Day media is bounded on desktop/laptop and naturally stacks on small screens.
- The vertical timeline uses a stable single-side chronology instead of an alternating layout.
- Filters and utility modules use softer, simpler surfaces rather than dashboard-like framing.
- Gutenberg offers four one-click appearance recipes before exposing granular controls.
- The visible pattern library is curated to 12 useful starting points. Legacy patterns remain registered for compatibility but are hidden from the inserter.
- Curated patterns rely on dynamic Popped blocks and inherited/native styling instead of hard-coded decorative colours.
- Admin and editor CSS now follows a quieter WordPress-like hierarchy with fewer visual containers competing for attention.

## Compatibility and risk

No content model, post type, query architecture, public REST endpoint or destructive setup behaviour was introduced in this pass. Existing saved patterns/blocks remain registered. The main risk is visual rather than data-related, which is why a final live Twenty Twenty-Five browser pass is still required before calling the build production-verified.
