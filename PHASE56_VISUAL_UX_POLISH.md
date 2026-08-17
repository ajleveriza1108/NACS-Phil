# NACS-Phil Phase 56 - Visual UX & Encoding Polish

Phase 56 is a visual acceptance repair on top of the merged Phase 53-55 release.

## Homepage readability

The Phase 18 homepage source intentionally used extremely small display text in several blocks. Phase 56 adds a final, scoped visual-polish layer that increases the readability of:

- Christian Values / Caring Teachers / Safe Learning Environment;
- the Learn Our Story action;
- Biblical Foundation / Academic Excellence / Character Formation / Community;
- supporting value descriptions;
- Latest News;
- Upcoming Events;
- announcement and event card titles, labels, descriptions, dates, and metadata.

The About values are forced into a robust single-column list so labels never collapse into letter-by-letter wrapping at intermediate tablet/mobile widths.

## Resources menu

The desktop Resources menu previously rendered both the browser/details affordance and a custom CSS caret. Phase 56 suppresses the marker/caret source directly while preserving the clickable dropdown behavior and keyboard semantics.

## Encoding / special characters

The News illustration contained a visible mojibake sequence:

`NACS-PHIL Â· NEWS + ANNOUNCEMENTS`

It is replaced with the ASCII-safe label:

`NACS-PHIL - NEWS + ANNOUNCEMENTS`

A regression test scans current public Blade templates and current public assets for common mojibake markers.

## Learning Tools

Phase 53 functionality is unchanged.

Phase 56 completes its presentation by:

- loading the established shared public shell CSS and JS on Learning Tools;
- keeping the Learning Tools page-specific bundle;
- preventing the Dictionary card from stretching to the Grammar card height;
- improving hero, form, card, privacy, result, provider, focus, mobile, and reduced-motion presentation;
- adding Dictionary & Grammar to the footer School Resources list.

## Safety

No database migrations, Composer dependency changes, authentication changes, PDF authorization changes, or external-provider behavior changes are part of Phase 56.
