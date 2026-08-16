@extends('admin.layouts.app', ['title' => 'Dashboard'])

@section('content')
@php($user = auth()->user())

<section class="p13-dashboard-hero">
    <div>
        <span class="cm-eyebrow">Good day, {{ $user->name }}</span>
        <h1>School Manager</h1>
        <span class="p13-sr-only">Administration Dashboard</span>
        <p>Your dashboard shows only the school tools assigned to your account.</p>
        <div class="p13-hero-meta">
            <span class="p13-role-chip">{{ $user->staffRoleLabel() }}</span>
            <span>{{ $user->is_active !== false ? 'Active staff account' : 'Inactive account' }}</span>
        </div>
    </div>
    <div class="p13-hero-actions">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Website &nearr;</a>
        <a href="{{ route('admin.security.index') }}" class="cm-button cm-button--secondary">Account Security</a>
    </div>
</section>

@if($databaseSetupIncomplete)
<section class="cm-alert" role="status">
    <strong>Local database setup is incomplete.</strong>
    {{ $missingTableCount }} authorized data area(s) are not initialized in this local SQLite database, so unavailable dashboard metrics are temporarily hidden.
    No database migration was performed automatically.
</section>
@endif

@if($showPriorityInbox)
<section class="p13-section" aria-labelledby="priority-heading">
    <div class="p13-section-head">
        <div>
            <span class="cm-eyebrow">Priority inbox</span>
            <h2 id="priority-heading">What needs attention</h2>
        </div>
        <span class="p13-section-note">Live counts from the school backend</span>
    </div>

    <div class="p13-priority-grid">
        @if($user->hasStaffPermission('governance.manage'))
            <a class="p13-priority-card {{ ($counts['pending_content_reviews'] ?? 0) > 0 ? 'has-work' : '' }}" href="{{ route('admin.reviews.index') }}">
                <span class="p13-priority-icon">R</span>
                <span>
                    <strong>{{ $counts['pending_content_reviews'] ?? 0 }}</strong>
                    <b>Content reviews</b>
                    <small>Teacher posts waiting for Principal/Super Admin review</small>
                </span>
                <i aria-hidden="true">&rarr;</i>
            </a>
        @endif

        @if($user->hasStaffPermission('admissions.manage'))
            <a class="p13-priority-card {{ ($counts['admissions_waiting_review'] ?? 0) > 0 ? 'has-work' : '' }}" href="{{ route('admin.admissions.index', ['status' => 'submitted']) }}">
                <span class="p13-priority-icon">A</span>
                <span>
                    <strong>{{ $counts['admissions_waiting_review'] ?? 0 }}</strong>
                    <b>Admissions waiting review</b>
                    <small>New or actively reviewed applications</small>
                </span>
                <i aria-hidden="true">&rarr;</i>
            </a>

            <a class="p13-priority-card {{ ($counts['due_followups'] ?? 0) > 0 ? 'has-work' : '' }}" href="{{ route('admin.inquiries.index', ['status' => 'follow_up']) }}">
                <span class="p13-priority-icon">I</span>
                <span>
                    <strong>{{ $counts['due_followups'] ?? 0 }}</strong>
                    <b>Inquiry follow-ups due</b>
                    <small>Families with a follow-up date due now</small>
                </span>
                <i aria-hidden="true">&rarr;</i>
            </a>
        @endif
    </div>
</section>
@endif

<section class="p13-section" aria-labelledby="quick-heading">
    <div class="p13-section-head">
        <div>
            <span class="cm-eyebrow">Role-based actions</span>
            <h2 id="quick-heading">Start common work</h2>
        </div>
        <span class="p13-section-note">{{ $user->staffRoleDescription() }}</span>
    </div>

    <div class="p13-quick-grid">
        @if($user->hasStaffPermission('news.manage'))
            <a href="{{ route('admin.announcements.create') }}" class="p13-quick-card"><span>+</span><div><strong>Announcement</strong><small>News, reminders, and school notices</small></div></a>
        @endif

        @if($user->hasStaffPermission('events.manage'))
            <a href="{{ route('admin.events.create') }}" class="p13-quick-card"><span>+</span><div><strong>School Event</strong><small>Date, venue, and activity information</small></div></a>
        @endif

        @if($user->hasStaffPermission('media.manage'))
            <a href="{{ route('admin.gallery.create') }}" class="p13-quick-card"><span>+</span><div><strong>Gallery Photo</strong><small>Consent-protected school photography</small></div></a>
            <a href="{{ route('admin.media.create') }}" class="p13-quick-card"><span>M</span><div><strong>Media Library</strong><small>Upload a reusable approved image</small></div></a>
        @endif

        @if($user->hasStaffPermission('faculty.manage'))
            <a href="{{ route('admin.faculty.create') }}" class="p13-quick-card"><span>F</span><div><strong>Faculty Profile</strong><small>Approved public staff presentation</small></div></a>
        @endif

        @if($user->hasStaffPermission('documents.manage'))
            <a href="{{ route('admin.documents.create') }}" class="p13-quick-card"><span>D</span><div><strong>School Document</strong><small>Forms, handbooks, and public downloads</small></div></a>
        @endif

        @if($user->hasStaffPermission('calendar.manage'))
            <a href="{{ route('admin.calendar.create') }}" class="p13-quick-card"><span>C</span><div><strong>Calendar Date</strong><small>Academic dates, holidays, and exams</small></div></a>
        @endif

        @if($user->hasStaffPermission('website.home'))
            <a href="{{ route('admin.website-content.edit') }}" class="p13-quick-card"><span>W</span><div><strong>Website Content</strong><small>Homepage and public page content</small></div></a>
        @endif

        @if($user->hasStaffPermission('staff.manage'))
            <a href="{{ route('admin.staff.index') }}" class="p13-quick-card"><span>+</span><div><strong>Staff Access Plan</strong><small>2 administrators + 7 specialized editor seats</small></div></a>
        @endif
    </div>
</section>

@if($counts !== [])
<section class="p13-section" aria-labelledby="numbers-heading">
    <div class="p13-section-head">
        <div><span class="cm-eyebrow">At a glance</span><h2 id="numbers-heading">Your authorized activity</h2></div>
    </div>

    <div class="p13-metric-grid">
        @isset($counts['students'])<a href="{{ route('admin.students.index') }}"><strong>{{ $counts['students'] }}</strong><span>Students</span></a>@endisset
        @isset($counts['announcements'])<a href="{{ route('admin.announcements.index') }}"><strong>{{ $counts['announcements'] }}</strong><span>Announcements</span></a>@endisset
        @isset($counts['events'])<a href="{{ route('admin.events.index') }}"><strong>{{ $counts['events'] }}</strong><span>Events</span></a>@endisset
        @isset($counts['gallery'])<a href="{{ route('admin.gallery.index') }}"><strong>{{ $counts['gallery'] }}</strong><span>Gallery Photos</span></a>@endisset
        @isset($counts['new_inquiries'])<a href="{{ route('admin.inquiries.index') }}"><strong>{{ $counts['new_inquiries'] }}</strong><span>New Inquiries</span></a>@endisset
        @isset($counts['admission_applications'])<a href="{{ route('admin.admissions.index') }}"><strong>{{ $counts['admission_applications'] }}</strong><span>Applications</span></a>@endisset
        @isset($counts['faculty_published'])<a href="{{ route('admin.faculty.index') }}"><strong>{{ $counts['faculty_published'] }}</strong><span>Published Faculty</span></a>@endisset
        @isset($counts['public_documents'])<a href="{{ route('admin.documents.index') }}"><strong>{{ $counts['public_documents'] }}</strong><span>Public Documents</span></a>@endisset
        @isset($counts['staff'])<a href="{{ route('admin.staff.index') }}"><strong>{{ $counts['staff'] }}</strong><span>Privileged Staff Accounts</span></a>@endisset
        @isset($counts['super_admins'])<a href="{{ route('admin.staff.index') }}"><strong>{{ $counts['super_admins'] }}/2</strong><span>Super Administrators</span></a>@endisset
        @isset($counts['staff_without_2fa'])<a href="{{ route('admin.staff.index') }}"><strong>{{ $counts['staff_without_2fa'] }}</strong><span>Staff Without 2FA</span></a>@endisset
    </div>
</section>
@endif

@if($user->hasStaffPermission('admissions.manage'))
<section class="p13-work-grid">
    <article class="p13-panel">
        <div class="p13-panel-head"><div><span class="cm-eyebrow">Families</span><h2>Recent inquiries</h2></div><a href="{{ route('admin.inquiries.index') }}">Open CRM</a></div>
        <div class="p13-list">
            @forelse($recentInquiries as $inquiry)
                <a href="{{ route('admin.inquiries.show', $inquiry) }}"><span><strong>{{ $inquiry->guardian_name }}</strong><small>{{ $inquiry->level_interested }} &middot; {{ $inquiry->created_at->diffForHumans() }}</small></span><b>{{ $inquiry->assignedTo?->name ?: 'Unassigned' }}</b></a>
            @empty<div class="cm-empty">No family inquiries yet.</div>@endforelse
        </div>
    </article>

    <article class="p13-panel">
        <div class="p13-panel-head"><div><span class="cm-eyebrow">Admissions</span><h2>Recent applications</h2></div><a href="{{ route('admin.admissions.index') }}">Open Admissions</a></div>
        <div class="p13-list">
            @forelse($recentApplications as $application)
                <a href="{{ route('admin.admissions.show', $application) }}"><span><strong>{{ $application->student_name }}</strong><small>{{ $application->applying_for_level }} &middot; {{ $application->reference_code }}</small></span><b>{{ $application->statusLabel() }}</b></a>
            @empty<div class="cm-empty">No admissions applications yet.</div>@endforelse
        </div>
    </article>
</section>
@endif

@if($user->hasAnyStaffPermission(['events.manage','calendar.manage']))
<section class="p13-work-grid">
    @if($user->hasStaffPermission('events.manage'))
        <article class="p13-panel">
            <div class="p13-panel-head"><div><span class="cm-eyebrow">Events</span><h2>Upcoming school events</h2></div><a href="{{ route('admin.events.index') }}">Manage</a></div>
            <div class="p13-schedule-list">
                @forelse($upcomingEvents as $event)
                    <article><time datetime="{{ $event->starts_at->toAtomString() }}"><strong>{{ $event->starts_at->format('d') }}</strong><small>{{ $event->starts_at->format('M') }}</small></time><span><strong>{{ $event->title }}</strong><small>{{ $event->venue ?: 'Venue not specified' }}</small></span></article>
                @empty<div class="cm-empty">No upcoming events.</div>@endforelse
            </div>
        </article>
    @endif

    @if($user->hasStaffPermission('calendar.manage'))
        <article class="p13-panel">
            <div class="p13-panel-head"><div><span class="cm-eyebrow">Academic calendar</span><h2>Important dates</h2></div><a href="{{ route('admin.calendar.index') }}">Manage</a></div>
            <div class="p13-schedule-list">
                @forelse($upcomingAcademicDates as $entry)
                    <article><time datetime="{{ $entry->starts_at->toAtomString() }}"><strong>{{ $entry->starts_at->format('d') }}</strong><small>{{ $entry->starts_at->format('M') }}</small></time><span><strong>{{ $entry->title }}</strong><small>{{ \App\Models\AcademicCalendarEntry::CATEGORIES[$entry->category] ?? $entry->category }}</small></span></article>
                @empty<div class="cm-empty">No upcoming academic dates.</div>@endforelse
            </div>
        </article>
    @endif
</section>
@endif

@if($user->isTeacher())
<section class="p13-panel">
    <span class="cm-eyebrow">Teacher access</span>
    <h2>Your publishing workflow</h2>
    <div class="p13-teacher-note">
        <strong>Create &rarr; Submit for Review &rarr; Principal Approval</strong>
        <p>You can prepare announcements, events, approved media, and authorized student records. School settings, staff accounts, family inquiries, and private admissions records remain restricted.</p>
    </div>
</section>
@endif
@endsection
