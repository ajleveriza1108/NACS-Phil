<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071f3d">
    <title>{{ isset($title) ? $title . ' | ' : '' }}NACS-Phil School Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/admin-content-manager/manager.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase9-admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase13-admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase15-launch/launch.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase16-branding/branding.css') }}">
    <script src="{{ asset('assets/admin-content-manager/manager.js') }}" defer></script>
    <script src="{{ asset('assets/phase13-admin/admin.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase17-theme/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase22-media/media.css') }}">
    <script src="{{ asset('assets/phase22-media/media.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase24-release/release-hardening.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase41-sis/sis.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase45-motion/motion.css') }}">
    <script src="{{ asset('assets/phase45-motion/motion.js') }}" defer></script>
</head>
@php($staffUser = auth()->user())
<body class="cm-body p13-admin">
<a href="#admin-main" class="p13-skip">Skip to administration content</a>

<div class="cm-app">
    <aside class="cm-sidebar" data-cm-sidebar data-p13-sidebar>
        <div class="cm-sidebar__top">
            <a href="{{ route('admin.dashboard') }}" class="cm-brand" aria-label="NACS-Phil School Manager home">
                <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="48" height="48">
                <span><strong>NACS-Phil</strong><small>School Manager</small></span>
            </a>
            <button type="button" class="cm-sidebar-close" data-cm-close aria-label="Close menu">&times;</button>
        </div>

        <label class="p13-nav-search">
            <span class="p13-sr-only">Find an administration tool</span>
            <input type="search" placeholder="Find a tool..." autocomplete="off" data-p13-nav-search>
        </label>

        <div class="p13-role-summary">
            <span class="p13-status-dot" aria-hidden="true"></span>
            <span><strong>{{ $staffUser->staffRoleLabel() }}</strong><small>{{ $staffUser->email }}</small></span>
        </div>

        <div data-p13-nav-groups>
            <p class="cm-sidebar-label" data-p13-nav-heading>My Workspace</p>
            <nav class="cm-nav" aria-label="My workspace">
                <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])>
                    <span class="cm-nav-icon">H</span><span>Dashboard</span>
                </a>

                @if($staffUser->hasStaffPermission('students.manage'))
                    <a href="{{ route('admin.students.index') }}" @class(['is-active' => request()->routeIs('admin.students.*')])><span class="cm-nav-icon">S</span><span>Student Records</span></a>
                @endif

                @if($staffUser->hasStaffPermission('news.manage'))
                    <a href="{{ route('admin.announcements.index') }}" @class(['is-active' => request()->routeIs('admin.announcements.*')])><span class="cm-nav-icon">N</span><span>Announcements</span></a>
                @endif

                @if($staffUser->hasStaffPermission('events.manage'))
                    <a href="{{ route('admin.events.index') }}" @class(['is-active' => request()->routeIs('admin.events.*')])><span class="cm-nav-icon">E</span><span>Events</span></a>
                @endif

                @if($staffUser->hasStaffPermission('media.manage'))
                    <a href="{{ route('admin.gallery.index') }}" @class(['is-active' => request()->routeIs('admin.gallery.*')])><span class="cm-nav-icon">P</span><span>Photos</span></a>
                    <a href="{{ route('admin.media.index') }}" @class(['is-active' => request()->routeIs('admin.media.*')])><span class="cm-nav-icon">M</span><span>Media Library</span></a>
                    <a href="{{ route('admin.facebook-media.index') }}" @class(['is-active' => request()->routeIs('admin.facebook-media.*')])><span class="cm-nav-icon">V</span><span>Live &amp; Videos</span></a>
                @endif
            </nav>

            @if($staffUser->hasAnyStaffPermission(['governance.manage','admissions.manage','faculty.manage','documents.manage','calendar.manage']))
                <p class="cm-sidebar-label" data-p13-nav-heading>School Office</p>
                <nav class="cm-nav" aria-label="School office">
                    @if($staffUser->hasStaffPermission('governance.manage'))
                        <a href="{{ route('admin.reviews.index') }}" @class(['is-active' => request()->routeIs('admin.reviews.*')])><span class="cm-nav-icon">R</span><span>Content Reviews</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('admissions.manage'))
                        <a href="{{ route('admin.admissions.index') }}" @class(['is-active' => request()->routeIs('admin.admissions.*')])><span class="cm-nav-icon">A</span><span>Applications</span></a>
                        <a href="{{ route('admin.inquiries.index') }}" @class(['is-active' => request()->routeIs('admin.inquiries.*')])><span class="cm-nav-icon">I</span><span>Inquiry CRM</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('faculty.manage'))
                        <a href="{{ route('admin.faculty.index') }}" @class(['is-active' => request()->routeIs('admin.faculty.*')])><span class="cm-nav-icon">F</span><span>Faculty &amp; Staff</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('documents.manage'))
                        <a href="{{ route('admin.documents.index') }}" @class(['is-active' => request()->routeIs('admin.documents.*')])><span class="cm-nav-icon">D</span><span>Documents</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('calendar.manage'))
                        <a href="{{ route('admin.calendar.index') }}" @class(['is-active' => request()->routeIs('admin.calendar.*')])><span class="cm-nav-icon">C</span><span>Academic Calendar</span></a>
                    @endif
                </nav>
            @endif

            @if($staffUser->hasAnyStaffPermission([
                'website.home','website.about','website.programs','website.admissions',
                'website.news','website.events','website.gallery','website.contact',
                'branding.manage','seo.manage','settings.manage','governance.manage'
            ]))
                <p class="cm-sidebar-label" data-p13-nav-heading>Website</p>
                <nav class="cm-nav" aria-label="Website management">
                    @if($staffUser->hasStaffPermission('website.home'))<a href="{{ route('admin.website-content.edit') }}" @class(['is-active' => request()->routeIs('admin.website-content.*')])><span class="cm-nav-icon">W</span><span>Homepage</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.about'))<a href="{{ route('admin.about-content.edit') }}" @class(['is-active' => request()->routeIs('admin.about-content.*')])><span class="cm-nav-icon">A</span><span>About Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.programs'))<a href="{{ route('admin.programs-content.edit') }}" @class(['is-active' => request()->routeIs('admin.programs-content.*')])><span class="cm-nav-icon">P</span><span>Programs Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.admissions'))<a href="{{ route('admin.admissions-content.edit') }}" @class(['is-active' => request()->routeIs('admin.admissions-content.*')])><span class="cm-nav-icon">D</span><span>Admissions Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.news'))<a href="{{ route('admin.news-content.edit') }}" @class(['is-active' => request()->routeIs('admin.news-content.*')])><span class="cm-nav-icon">N</span><span>News Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.events'))<a href="{{ route('admin.events-content.edit') }}" @class(['is-active' => request()->routeIs('admin.events-content.*')])><span class="cm-nav-icon">E</span><span>Events Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.gallery'))<a href="{{ route('admin.gallery-content.edit') }}" @class(['is-active' => request()->routeIs('admin.gallery-content.*')])><span class="cm-nav-icon">G</span><span>Gallery Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('website.contact'))<a href="{{ route('admin.contact-content.edit') }}" @class(['is-active' => request()->routeIs('admin.contact-content.*')])><span class="cm-nav-icon">C</span><span>Contact Page</span></a>@endif
                    @if($staffUser->hasStaffPermission('branding.manage'))<a href="{{ route('admin.branding.edit') }}" @class(['is-active' => request()->routeIs('admin.branding.*')])><span class="cm-nav-icon">B</span><span>Branding</span></a>@endif
                    @if($staffUser->hasStaffPermission('seo.manage'))<a href="{{ route('admin.seo.edit') }}" @class(['is-active' => request()->routeIs('admin.seo.*')])><span class="cm-nav-icon">O</span><span>SEO &amp; Sharing</span></a>@endif
                    @if($staffUser->hasStaffPermission('settings.manage'))
                        <a href="{{ route('admin.settings.edit') }}" @class(['is-active' => request()->routeIs('admin.settings.*')])><span class="cm-nav-icon">S</span><span>School Settings</span></a>
                        <a href="{{ route('admin.launch-readiness') }}" @class(['is-active' => request()->routeIs('admin.launch-readiness')])><span class="cm-nav-icon">L</span><span>Launch Readiness</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('governance.manage'))
                        <a href="{{ route('admin.trash.index') }}" @class(['is-active' => request()->routeIs('admin.trash.*')])><span class="cm-nav-icon">T</span><span>Trash</span></a>
                        <a href="{{ route('admin.audit.index') }}" @class(['is-active' => request()->routeIs('admin.audit.*')])><span class="cm-nav-icon">H</span><span>Audit History</span></a>
                    @endif
                </nav>
            @endif

            <p class="cm-sidebar-label" data-p13-nav-heading>My Account</p>
            <nav class="cm-nav" aria-label="My account">
                <a href="{{ route('admin.security.index') }}" @class(['is-active' => request()->routeIs('admin.security.*')])>
                    <span class="cm-nav-icon">L</span><span>Login &amp; Security</span>
                </a>
            </nav>

            @if($staffUser->hasAnyStaffPermission(['staff.manage','system.manage']))
                <p class="cm-sidebar-label" data-p13-nav-heading>System</p>
                <nav class="cm-nav" aria-label="System administration">
                    @if($staffUser->hasStaffPermission('staff.manage'))
                        <a href="{{ route('admin.staff.index') }}" @class(['is-active' => request()->routeIs('admin.staff.*')])><span class="cm-nav-icon">S</span><span>Staff Accounts</span></a>
                    @endif
                    @if($staffUser->hasStaffPermission('system.manage'))
                        <a href="{{ route('admin.system-health') }}" @class(['is-active' => request()->routeIs('admin.system-health')])><span class="cm-nav-icon">+</span><span>System Health</span></a>
                    @endif
                </nav>
            @endif
        </div>

        <div class="cm-sidebar__bottom">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-view-site">View public website <span>&nearr;</span></a>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="cm-signout">Sign out</button></form>
        </div>
    </aside>

    <div class="cm-main">
        <header class="cm-topbar p13-topbar">
            <div class="cm-topbar__left">
                <button type="button" class="cm-menu-button" data-cm-open aria-label="Open administration menu"><span></span><span></span><span></span></button>
                <div><small>NACS-Phil School Manager</small><strong>{{ $staffUser->name }}</strong></div>
            </div>
            <div class="p13-topbar-actions">
                <span class="p13-role-pill">{{ $staffUser->staffRoleLabel() }}</span>
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="p13-preview-link">Preview Site <span aria-hidden="true">&nearr;</span></a>
            </div>
        </header>

        <main id="admin-main" class="cm-content p13-content">
            @if($staffUser->requiresTwoFactorRecommendation() && !$staffUser->twoFactorEnabled())
                <div class="cm-alert p12-security-note">
                    <strong>Security requirement before production:</strong>
                    enable two-factor authentication for this privileged staff account.
                    <a href="{{ route('admin.security.index') }}">Open Login &amp; Security</a>.
                </div>
            @endif

            @if($staffUser->force_password_reset)
                <div class="cm-alert cm-alert--error">
                    <strong>Password change required.</strong>
                    Please update your password in <a href="{{ route('admin.security.index') }}">Login &amp; Security</a>.
                </div>
            @endif

            @if(session('success'))<div class="cm-alert cm-alert--success" role="status">{{ session('success') }}</div>@endif
            @if(session('warning'))<div class="cm-alert" role="alert">{{ session('warning') }}</div>@endif

            @if($errors->any())
                <div class="cm-alert cm-alert--error" role="alert">
                    <strong>Please check these items:</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div class="cm-backdrop" data-cm-backdrop hidden></div>
</body>
</html>
