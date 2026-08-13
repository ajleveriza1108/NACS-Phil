@extends('admin.layouts.app', ['title' => 'Content Manager'])

@section('content')
<section class="cm-page-head p9-dashboard-head">
    <div>
        <span class="cm-eyebrow">Good day, {{ auth()->user()->name }}</span>
        <h1>School Content Manager</h1>
        <span class="cm-screen-reader-only">Administration Dashboard</span>
        <p>Your available tools are based on your staff role. Website design and developer settings remain protected.</p>
        <div class="p9-role-line">
            <span class="p9-role-chip">{{ auth()->user()->staffRoleLabel() }}</span>
            <span>{{ auth()->user()->is_active !== false ? 'Active account' : 'Inactive account' }}</span>
        </div>
    </div>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview website &nearr;</a>
</section>

<section class="cm-quick-grid" aria-label="Quick actions">
    <a href="{{ route('admin.announcements.create') }}" class="cm-quick-card cm-quick-card--blue">
        <span class="cm-quick-card__icon">+</span>
        <span><strong>Post Announcement</strong><small>News, reminders, enrollment notices</small></span>
        <b>&rarr;</b>
    </a>

    <a href="{{ route('admin.events.create') }}" class="cm-quick-card cm-quick-card--gold">
        <span class="cm-quick-card__icon">+</span>
        <span><strong>Add School Event</strong><small>Date, time, venue, and details</small></span>
        <b>&rarr;</b>
    </a>

    <a href="{{ route('admin.gallery.create') }}" class="cm-quick-card cm-quick-card--green">
        <span class="cm-quick-card__icon">+</span>
        <span><strong>Upload Photos</strong><small>Approved photos with authorization and consent</small></span>
        <b>&rarr;</b>
    </a>

    @if($canManageSettings)
        <a href="{{ route('admin.website-content.edit') }}" class="cm-quick-card cm-quick-card--navy">
            <span class="cm-quick-card__icon">W</span>
            <span><strong>Edit Website</strong><small>Homepage and official page settings</small></span>
            <b>&rarr;</b>
        </a>
    @endif

    @if($canManageStaff)
        <a href="{{ route('admin.staff.index') }}" class="cm-quick-card cm-quick-card--navy">
            <span class="cm-quick-card__icon">S</span>
            <span><strong>Staff Accounts</strong><small>Principal and teacher access</small></span>
            <b>&rarr;</b>
        </a>
    @endif
</section>

<section class="cm-summary-grid">
    <article class="cm-panel">
        <div class="cm-panel__head">
            <div><span class="cm-eyebrow">At a glance</span><h2>Website activity</h2></div>
        </div>
        <div class="cm-stat-grid p9-stat-grid">
            <a href="{{ route('admin.announcements.index') }}"><strong>{{ $counts['announcements'] ?? 0 }}</strong><span>Announcements</span></a>
            <a href="{{ route('admin.events.index') }}"><strong>{{ $counts['events'] ?? 0 }}</strong><span>Events</span></a>
            <a href="{{ route('admin.gallery.index') }}"><strong>{{ $counts['gallery'] ?? 0 }}</strong><span>Photos</span></a>
            @if($canManageSettings)
                <a href="{{ route('admin.inquiries.index') }}"><strong>{{ $counts['new_inquiries'] ?? 0 }}</strong><span>New inquiries</span></a>
            @endif
            @if($canManageStaff)
                <a href="{{ route('admin.staff.index') }}"><strong>{{ $counts['staff'] ?? 0 }}</strong><span>Staff accounts</span></a>
            @endif
        </div>

        @if(isset($counts['admission_applications']) && \Illuminate\Support\Facades\Route::has('admin.admissions.index') && $canManageSettings)
            <a href="{{ route('admin.admissions.index') }}" class="cm-application-strip">
                <span><strong>{{ $counts['admission_applications'] }}</strong> admissions applications</span>
                <span>{{ $counts['admissions_waiting_review'] ?? 0 }} waiting review &rarr;</span>
            </a>
        @endif
    </article>

    @if($canManageSettings)
    <article class="cm-panel">
        <div class="cm-panel__head">
            <div><span class="cm-eyebrow">Recent</span><h2>Family inquiries</h2></div>
            <a href="{{ route('admin.inquiries.index') }}">View all</a>
        </div>
        <div class="cm-simple-list">
            @forelse($recentInquiries as $inquiry)
                <a href="{{ route('admin.inquiries.show', $inquiry) }}">
                    <span><strong>{{ $inquiry->guardian_name }}</strong><small>{{ $inquiry->level_interested }} &middot; {{ $inquiry->created_at->diffForHumans() }}</small></span>
                    <b>{{ str_replace('_', ' ', $inquiry->status) }}</b>
                </a>
            @empty
                <div class="cm-empty">No inquiries yet.</div>
            @endforelse
        </div>
    </article>
    @else
    <article class="cm-panel">
        <div class="cm-panel__head">
            <div><span class="cm-eyebrow">Your access</span><h2>Teacher content tools</h2></div>
        </div>
        <div class="p9-access-note">
            <strong>Daily publishing tools are available.</strong>
            <p>You can manage announcements, events, and approved gallery photos. Official school settings and family inquiries are limited to the Principal and Super Admin.</p>
        </div>
    </article>
    @endif
</section>

<section class="cm-panel cm-panel--wide">
    <div class="cm-panel__head">
        <div><span class="cm-eyebrow">Calendar</span><h2>Upcoming events</h2></div>
        <a href="{{ route('admin.events.index') }}">Manage events</a>
    </div>
    <div class="cm-event-strip">
        @forelse($upcomingEvents as $event)
            <article>
                <span>{{ $event->starts_at->format('M d') }}</span>
                <strong>{{ $event->title }}</strong>
                <small>{{ $event->venue ?: 'Venue not specified' }}</small>
            </article>
        @empty
            <div class="cm-empty">No upcoming events. Add one using the shortcut above.</div>
        @endforelse
    </div>
</section>
@endsection