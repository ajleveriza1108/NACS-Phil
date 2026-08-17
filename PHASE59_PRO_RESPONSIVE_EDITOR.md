# Phase 59 - Pro Responsive Editor + Smart Text Fit

## Immediate mobile repair

The Homepage "Why Choose NACS-Phil" value labels now use a responsive grid and
single-line fit contract so these phrases remain intact on narrow screens:

- Christian Values
- Caring Teachers
- Safe Learning

The icon and label get separate grid tracks, while label typography uses
responsive `clamp()` sizing. Phone and very-small-phone breakpoints reduce the
font before a two-word label is allowed to split awkwardly.

## Professional responsive inspector

The existing Premium Smart Editor is extended with a live professional
inspector. Editors can select an editable text element from the preview or the
content panel and tune safe responsive presentation for:

- Desktop Base
- Tablet
- Phone

Available controls:

- Smart Auto Fit
- Fit All Alerts
- responsive fit-health warnings
- font size
- line height
- letter spacing
- font weight
- text alignment
- responsive wrap / keep-on-one-line / balanced heading
- text-frame maximum width
- text-frame minimum height
- horizontal padding
- vertical padding
- reset current device style
- reset all styles for the selected element

## Safety model

This is not a raw HTML/CSS/code editor.

The browser stores only a structured JSON style model. The server then
allowlists:

- editable field names
- responsive scopes
- permitted properties
- enum values
- numeric ranges

Only server-generated CSS is published.

Dashboard, operational school-management pages, permissions, routes, raw code,
navigation structure, and arbitrary CSS remain outside the visual editor.

## Persistence

Responsive styles use the existing `SiteContent` table under the reserved
`__editor_home` namespace. No migration is needed.

Revision History now stores:

- content
- hidden fields
- responsive styles

Restoring a revision restores all three. Restoring original defaults clears
custom responsive styles.

## Release strategy

Local validation is intentionally targeted so the workstation does not repeat
the entire suite unnecessarily. The GitHub pull-request Quality Gate remains
the authoritative complete suite/audit/build gate before merge.
