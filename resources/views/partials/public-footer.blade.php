<footer class="nacs11-footer" data-nacs11-footer>
    <div class="nacs11-shell nacs11-footer__grid">
        <section class="nacs11-footer__brand">
            <a href="{{ route('home') }}" class="nacs11-brand nacs11-brand--footer">
                <span class="nacs11-brand__mark">
                    <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="46" height="46">
                </span>
                <span class="nacs11-brand__copy">
                    <strong>{{ \App\Models\SchoolSetting::valueFor('short_name', config('nacs.short_name')) }}</strong>
                    <small>Noel Academy Christian of Sariaya Philippines, Inc.</small>
                </span>
            </a>
            <p>{{ \App\Models\SchoolSetting::valueFor('tagline', 'Faith. Character. Excellence.') }}</p>
            <span class="nacs11-footer__note">Christ-centered education, careful communication, and responsible stewardship.</span>
        </section>

        <section>
            <h2>Explore</h2>
            <div class="nacs11-footer__links">
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('programs') }}">Programs</a>
                <a href="{{ route('admissions') }}">Admissions</a>
                <a href="{{ route('faculty.index') }}">Faculty &amp; Staff</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </section>

        <section>
            <h2>School Resources</h2>
            <div class="nacs11-footer__links">
                <a href="{{ route('calendar.index') }}">Academic Calendar</a>
                <a href="{{ route('documents.index') }}">Documents</a>
                <a href="{{ route('announcements.index') }}">News</a>
                <a href="{{ route('events.index') }}">Events</a>
                <a href="{{ route('gallery.index') }}">Gallery</a>
                <a href="{{ route('media.index') }}">Live &amp; Videos</a>
                <a href="{{ route('privacy') }}">Privacy</a>
            </div>
        </section>

        <section>
            <h2>Location</h2>
            <p>{{ \App\Models\SchoolSetting::valueFor('address', config('nacs.address')) }}</p>
            @if(\App\Models\SchoolSetting::valueFor('office_hours'))<p>{{ \App\Models\SchoolSetting::valueFor('office_hours') }}</p>@endif
            <a class="nacs11-footer__inline" href="{{ route('contact') }}">Contact the school <span aria-hidden="true">&rarr;</span></a>
        </section>
    </div>

    <div class="nacs11-shell nacs11-footer__bottom">
        <span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span>
        @if(app()->environment('production'))
            <span>Privacy, child protection, and responsible stewardship remain part of our public commitment.</span>
        @else
            <span>Official content must be reviewed before public launch.</span>
        @endif
    </div>
</footer>
