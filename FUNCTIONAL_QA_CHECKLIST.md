# NACS-Phil Full Functional Browser QA — Phase 27

Automated Laravel tests cannot physically click browser UI, invoke the native file chooser, or prove a third-party Facebook player/fullscreen control works on a real device. This checklist is the required manual browser counterpart to the automated Phase 27 integration suite.

Mark an item complete only after actually using the control.

## Public shared navigation

Desktop:

- Logo returns to Home.
- Home, About, Programs, Admissions, News, Events, Gallery, and Contact all open the correct page.
- Resources expands and Faculty & Staff, Academic Calendar, Documents, and Live & Videos all open.
- Enroll Now opens Admissions.
- Current-page styling/`aria-current` is correct.

Mobile at 320px and 360px:

- hamburger opens the navigation.
- hamburger closes the navigation.
- selecting a link closes the navigation.
- Escape closes the navigation when a keyboard is available.
- rotating/resizing to desktop does not leave the mobile menu stuck open.
- all mobile links open the correct page.
- no menu item/button is cropped or horizontally off-screen.

## Home

- every hero CTA opens its intended destination.
- Admissions, Calendar, Faculty, Documents, and Inquiry quick links work.
- featured News/Event/Gallery links open their real detail/page destinations.
- no placeholder/fake content is exposed.

## About / Programs / Admissions

- all CTA links work.
- program cards/content are readable and do not point to missing pages.
- Admissions Apply opens the application.
- Track Application opens tracking.
- any FAQ/accordion interactions open/close correctly where present.

## News

- index opens.
- featured and regular article links open the correct detail.
- pagination works when enough records exist.
- detail Back/News link works.

## Events

- index opens.
- event detail opens.
- venue/date/time render correctly.
- external registration link, when configured, opens safely in a new tab.
- pagination works.

## Gallery

- category filters work.
- image button opens the lightbox.
- Previous works.
- Next works.
- Close works.
- Escape closes.
- Left/Right arrow keys navigate.
- clicking the backdrop closes.
- keyboard focus returns to the image button after closing.
- Photo Privacy links open Privacy.
- images do not appear unless publication/consent rules permit them.

## Contact

- required validation appears when fields are missing.
- privacy consent is required.
- valid inquiry submits once and shows success.
- honeypot remains invisible to normal users.
- no duplicate submission is created from one normal click.

## Faculty / Calendar / Documents

Faculty:
- department filters work.
- profile cards/photos load.

Calendar:
- school-year filter works.
- category filter works.
- Apply Filter works.
- pagination works.

Documents:
- public download button downloads/opens the intended file.
- private/staff files cannot be downloaded publicly.
- pagination works.

## Live & Videos

Admin:
- paste a real Public Facebook recorded-video URL.
- Facebook preview appears.
- invalid/non-Facebook URL is rejected.
- Teacher save remains Draft.
- Principal/Super Admin can publish after Public/embed confirmation.

Public:
- thumbnail/player appears.
- Play works inside NACS-Phil.
- fullscreen works when Facebook/browser permits it.
- Watch on Facebook opens the intended Facebook item.
- test both desktop and phone.
- test one real Facebook Live/replay.
- confirm the website does not upload/store the video binary.

## Admissions family workflow

- Apply form validates required fields.
- consent boxes are required.
- successful application produces receipt.
- reference code is visible.
- one-time access code is visible only where intended.
- wrong access code is rejected.
- correct reference/access code opens private status.
- Track logout returns to tracking page.
- requested-document upload stays blocked until staff requests documents.
- when requested, PDF/JPG/PNG upload works within 5 MB.
- invalid type or oversize file is rejected.
- family can delete its eligible uploaded document when the workflow allows it.
- private pages are not cached by the browser after logout/back navigation as far as the browser permits.

## School Manager authentication/security

- Admin Login works.
- wrong password is rejected.
- Logout works.
- 2FA setup works.
- 2FA challenge works.
- password change works.
- revoke other sessions works.
- Teacher, Principal, Super Admin permissions remain different as designed.

## School Manager daily content

Announcements:
- list, Add, Save Draft/Submit/Publish as role permits, Edit, Delete.
- published item appears publicly.
- deleted item moves to Safe Trash.

Events:
- list, Add, Edit, Delete.
- public detail appears only when published.

Photos:
- Upload, preview/image display, Edit, Delete.
- consent/publication protection works.

Live & Videos:
- Add, preview, Draft, Publish, Edit, Delete.

Media Library:
- index, upload/create, delete work where permitted.

## Leadership tools

- Safe Trash opens.
- Restore works.
- permanent delete is Super Admin only.
- Audit History opens and records meaningful actions.
- Reviews opens.
- Principal approve/reject workflow changes public visibility correctly.
- Faculty list/create/edit/delete works.
- Documents list/create/edit/download/delete works.
- Calendar list/create/edit/delete works.
- Admissions list/detail/status update/checklist/document verification/private download/access-code rotation work.
- Inquiries list/detail/status/assignment/follow-up update work.
- Homepage Content editor saves.
- About Content editor saves.
- Programs Content editor saves.
- Admissions Content editor saves.
- News Content editor saves.
- Events Content editor saves.
- Gallery Content editor saves.
- Contact Content editor saves.
- SEO editor saves.
- Branding upload/remove works with approved image types.
- Launch Readiness opens and updates from real configuration.
- School Settings saves.

## Super Admin only

- Staff list opens.
- Add staff works.
- Edit staff works.
- Reset staff 2FA works.
- System Health opens.

## Final interaction/device matrix

Repeat the most important flows on:

- 320px phone.
- 360px phone.
- modern Android phone.
- portrait tablet.
- landscape tablet.
- laptop.
- desktop.
- ultrawide desktop.

For each, confirm:

- no cropped buttons.
- no invisible/off-screen controls.
- no horizontal overflow.
- keyboard focus is visible.
- forms can be completed.
- confirmation dialogs are understandable.
- Back navigation behaves reasonably.
- no dead links or 404 pages.
- no 500 errors.
- no broken characters.
- no duplicate navigation items.

## Release rule

NACS-Phil is not considered fully interaction-verified until:

1. Phase 27 automated integration tests pass.
2. the complete Laravel regression suite passes.
3. this browser checklist is completed on the intended staging/production environment.
4. Facebook controls are verified with real Public Facebook content.
5. the school completes Launch Readiness.
