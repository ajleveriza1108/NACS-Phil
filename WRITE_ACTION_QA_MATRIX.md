# NACS-Phil Phase 28 — Write-Action & Permission QA Matrix

Phase 27 verifies that pages, named routes, forms, buttons, and interaction assets are connected.

Phase 28 verifies the **write actions behind those controls** using Laravel's isolated test database and fake/test storage where appropriate.

## Automated write-action coverage

### Daily content

Announcements:
- create
- publish
- update
- soft delete
- Safe Trash restore
- Teacher submit for review
- Principal changes requested
- Teacher resubmit
- Principal approve

Events:
- create
- publish
- update
- soft delete
- Safe Trash restore

Photos:
- upload
- publish only with consent
- replace image
- old image cleanup
- soft delete
- image retained for recovery
- Safe Trash restore

Facebook Live & Videos:
- Teacher save is forced to Draft
- Teacher cannot delete
- Principal publish with Public/embed confirmation
- featured state
- soft delete
- Safe Trash restore

### Media Library

- upload with rights confirmation
- delete moves to Safe Trash instead of destroying the file
- restore keeps the file usable
- permanent delete removes the unused file
- active or soft-deleted Faculty/Gallery references block deletion

### Resources

Faculty & Staff:
- create
- update
- archive
- Safe Trash restore

Academic Calendar:
- create
- update
- archive
- Safe Trash restore

Documents:
- private server-side upload
- metadata update
- authorized Admin download
- archive while retaining file
- Safe Trash restore
- Super Admin permanent delete removes the private file

### Inquiry workflow

- status
- assignment
- follow-up date
- source
- interest level
- staff notes

### Admissions administration

- application status update
- public status message
- checklist completion
- private document download
- document verification
- access-code rotation

### Staff & security

- Super Admin creates Teacher/Principal account
- staff edit/security flags
- staff 2FA reset
- Super Admin accounts protected from staff editor
- password change
- 2FA setup
- 2FA confirmation with valid TOTP
- recovery-code disable
- revoke other sessions action

### Permission enforcement

Teacher is blocked from leadership/private areas.

Principal can restore Safe Trash items but cannot permanently delete.

Principal cannot manage staff accounts.

Only Super Admin can permanently delete Safe Trash content.

## Safe Trash scope after Phase 28

Safe Trash now exposes all current soft-deleted content managed by delete/archive buttons:

- Announcements
- Events
- Photos
- Live & Videos
- Faculty & Staff
- Documents
- Academic Calendar
- Media Library

This avoids a state where an administrator clicks Delete/Archive and the record becomes hidden without a recovery action.

## Browser/manual requirement

Automated tests exercise Laravel actions, validation, permissions, database effects, and storage effects. The final browser checklist from `FUNCTIONAL_QA_CHECKLIST.md` still applies for physical clicking, file-picker UI, confirmation dialogs, focus, mobile interactions, and real Facebook playback.
