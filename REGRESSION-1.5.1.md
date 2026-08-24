# Popped 1.5.1 regression patch

This release is intentionally limited to bugs and security hardening found in the 1.5.0 regression review.

## Fixed

- Password-protected story content cannot be exposed through Popped-generated excerpts.
- Protected stories are excluded from Popped content-source, related-story, chronology and manual discovery outputs.
- Existing saved count values are never rewritten by a heuristic migration.
- On This Day only uses previous years, can promote an eligible preferred/override story even when it is outside the first visible slice, and uses the full matching total for its “more events” link.
- Dedicated Horizontal Timeline blocks ignore visitor view/filter query parameters, stay horizontal and use a correct visible-result count when pagination is disabled.
- Year Navigator counts are loaded with one cached grouped query rather than one query per year.
- Horizontal timeline trackpad handling releases vertical scrolling at both rail boundaries.
- Discovery meta references are filtered by `read_post` capability on save and display.
- Repeatable components generate unique DOM IDs.

## Compatibility

No patterns, CSS design rules, typography, colours, spacing or layout defaults were changed in 1.5.1.

## Validation

- PHP syntax: 46/46 files passed `php -l`.
- JavaScript syntax: 3/3 files passed `node --check`.
- 30/30 pattern files are retained byte-for-byte from 1.5.0.
- All CSS files are retained byte-for-byte from 1.5.0.
- Static regression contracts pass for all eight findings fixed in this release.
