# NACS-Phil Phase 49 - Authorization and IDOR Hardening

Phase 49 extends the Phase 48 relationship-aware student protection into an explicit authorization regression baseline.

Implemented source gates cover:
- default-deny student visibility for unknown actors;
- active teacher-assignment scoping;
- parent/child object binding for grade deletion;
- subject-aware grade authorization;
- leadership-only finance mutation;
- leadership-only confidential student document registration;
- relationship authorization for report cards and transcripts;
- dedicated staff-management permission;
- separate family/staff admissions boundaries;
- future mobile API remaining disabled until it can reuse the same authorization model.

Command:

`php artisan nacs:authorization-baseline --strict`

No future API is activated by this phase.
