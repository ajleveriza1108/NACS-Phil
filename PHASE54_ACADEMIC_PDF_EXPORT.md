# NACS-Phil Phase 54 - Static A4 Academic PDF Export

Phase 54 adds true server-generated PDF downloads for:

- Student Report Card / Grades
- Transcript of Records (TOR)

The export uses Dompdf 3.1.x through Composer.

## Document rules
- A4 portrait output;
- static PDF output with no editable form fields;
- official school logo/header;
- school-controlled watermark;
- student identity and academic data;
- signature lines;
- generated-by and generated-at note;
- no-store response headers.

Official TOR export remains leadership-restricted exactly like the existing official TOR print view. Teachers/authorized student viewers can still produce the existing draft/unofficial academic-history PDF where permitted.

A PDF file cannot be made mathematically impossible to alter with specialized software. The school watermark, official/draft state, static rendering, and authorization rules make the exported copy controlled and visibly attributable. Future QR/cryptographic verification can be added as a separate issuance phase.
