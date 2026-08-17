# NACS-Phil Phase 56 - Final Visual Acceptance Repair

This patch addresses the two remaining browser-visible issues after Phase 56.

## Learning Tools hero title

The Learning Tools hero container already used white foreground color, but the
`h1` itself did not have an explicit color declaration. The shared public theme
contains explicit heading colors, so the title could render dark navy over the
dark blue hero.

The final repair explicitly locks `.lt-hero h1` to white and applies
`-webkit-text-fill-color: #fff` for consistent browser rendering.

## Resources indicator

The first Phase 56 repair removed `summary::after`, native details markers, and
the old menu indicator. The shared public theme still draws its actual desktop
Resources chevron with:

`.nacs16-resources > summary::before`

The final repair removes that pseudo-element directly while preserving the
`details/summary` dropdown behavior, focusability, and keyboard semantics.

## Scope

Only the final visual-polish stylesheet, its regression test, and this document
are changed. There are no database, Composer, authentication, PDF, Dictionary,
Grammar, or Header Manager behavior changes.
