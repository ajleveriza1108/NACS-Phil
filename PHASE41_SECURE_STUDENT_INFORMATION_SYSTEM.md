# Phase 41 — Secure Student Information System

Phase 41 adds a private student-information foundation while preserving the public NACS website.

## Roles and isolation

- **Super Admin / Principal:** school-wide student visibility and leadership-only controls.
- **Teacher:** only students assigned to that teacher. Subject-specific assignments may restrict grade entry.
- **Student:** only the student's own portal record.
- **Parent/Guardian:** only linked children.

Changing a URL or student identifier must never bypass server-side authorization.

## Structured records

The database stores compact structured records for:

- student identity and enrollment
- teacher/subject assignments
- grades, quizzes, projects and exam results
- attendance
- financial ledger and balances
- parent/guardian relationships
- external document metadata
- audit history

Teachers can register students and, when assigned, record academic/attendance information. Financial ledger changes, guardian linking, teacher assignment administration and highly confidential document registration are leadership-only.

## Information classification

- **Public:** normal approved website content.
- **Internal:** authenticated school-office information.
- **Confidential:** student profiles, academic history, grades, attendance and guardian relationships.
- **Highly Confidential:** finance and private document metadata.

Portal and student-record pages are private/no-store and noindex.

## Storage-saving rule

Confidential student documents must not consume permanent web-host storage.

`config/student_portal.php` sets external storage as the only supported student-document mode and explicitly disables a local fallback. The initial Phase 41 implementation stores only an approved external file identifier and metadata. It does not put the file itself in the Laravel host or database.

The future Google integration is deliberately credential-free in Git. When NACS later obtains/approves its `.edu.ph` domain and Google Workspace/Google-managed storage, production secrets and Shared Drive identifiers are configured outside source control.

## School email domain

`NACS_SCHOOL_EMAIL_DOMAIN` is optional. Until NACS registers and approves a real domain, student accounts may use the school-approved registered email. Once configured, newly created student portal accounts are restricted to that domain.

No `.edu.ph` name is hard-coded.

## Audit

Student-record audit entries record who performed an action, the affected record type, and which fields were involved. The audit avoids duplicating full confidential field values.

## External gates still required

Google Workspace/domain registration, actual cloud upload/download integration, production hosting, and manual browser/device acceptance require real external configuration and are not fabricated by this phase.
