# NACS-Phil Phase 58 - Premium Smart Homepage Editor

Phase 58 extends the existing Phase 47 Homepage Visual Editor. It does not create a second editor and does not make operational administration pages editable.

## Recovery and safety
- Undo / Redo with Ctrl+Z, Ctrl+Y, Ctrl+Shift+Z.
- Hide -> Undo feedback.
- Hidden Elements list with Restore and Restore All.
- Reset unsaved changes to the last published state.
- Restore original approved homepage with confirmation.
- Server Revision History, capped at 20 snapshots.
- Revision restore first preserves the currently-live state.
- Automatic local recovery draft and explicit Save Draft.
- Saved / Unsaved status and beforeunload/internal-navigation warnings.

## Protected pages
Premium visual-editor assets load only for `admin.website-content.*`. Dashboard, Security, Students, Grades, Finance, Admissions operations, Staff, Audit, System Health and other internal pages remain outside the visual editor.

## Storage
No migration. Live content remains under `site_contents.page=home`. Editor-only state uses `__editor_home` and `__editor_revision_home`.

## Permissions
The existing `staff_permission:website.home` route boundary remains mandatory.
