# Popped 1.3.1 editor UX audit

## Scope

This pass audits the Gutenberg editing experience for all 15 Popped blocks in WordPress 7.1-era editors. It focuses on inspector information architecture, progressive disclosure, reset behaviour, picker usability, preview fidelity and accessibility.

## Findings corrected

| Area | Before | 1.3.1 |
| --- | --- | --- |
| Inspector structure | Content, layout, images, typography, metadata and card styling were all mixed into Settings. | Editorial choices stay in Settings; appearance controls live in Styles. |
| Story details | Visibility toggles and visual typography were mixed together. | Story detail visibility is separate from title/excerpt/metadata styling. |
| Progressive disclosure | Crop focal point and metadata controls appeared even when they were irrelevant. | Focal point appears only for constrained cover crops; metadata styling explains when metadata is disabled; excerpt size appears only when excerpts are on. |
| Reset behaviour | One appearance reset lived under Advanced, mixed with content exclusions. | Each appearance section can reset itself, plus one clearly scoped Popped appearance reset in Styles. Native Gutenberg styles are preserved. |
| Exclusions | Content exclusions shared an Advanced panel with card gap and visual resets. | Exclusions are content-only; card gap moved to Cards & spacing. |
| Responsive controls | “Columns” looked like a fixed viewport promise. | Label now says “Columns on wide screens” and explains automatic smaller-screen reduction. |
| Picker usability | Chosen-post rows were dense and selection state was hard to scan. | Decoded titles, count summary, Clear all, safer wrapping, 44px rows and focus treatment. |
| Preview fidelity | Editor forced dates to 10px and used direct `.editor-styles-wrapper` selectors. | Editor-only text distortion removed; preview CSS is scoped to the Popped preview container. |
| Style ownership | Popped card controls and native Gutenberg block styles could be confused. | Help text consistently distinguishes inner story/card styling from outer Color, Typography, Dimensions and Border controls. |

## Inspector model

### Settings

1. Content
2. Story details
3. Layout & behaviour
4. Exclusions, where supported

### Styles

1. Native Gutenberg block-support controls
2. Popped appearance status/reset
3. Story images
4. Story typography
5. Metadata style
6. Cards & spacing

## Validation

- 46/46 PHP files pass `php -l`.
- 3/3 JavaScript files pass `node --check`.
- `wp-html-entities` is declared for the block editor script before decoded REST post titles are used.
- No direct `.editor-styles-wrapper` selector remains in `assets/css/editor.css`.
- Custom appearance controls are rendered under `InspectorControls group="styles"`.
- Content/layout/exclusion controls remain under `InspectorControls group="settings"`.
- Popped appearance reset does not remove native Gutenberg `style`, colour, gradient, font-size or border attributes.

A final browser smoke test inside the user’s exact WordPress 7.1/theme/plugin stack is still recommended because this environment does not provide the full logged-in WordPress editor runtime.
