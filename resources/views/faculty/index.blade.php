@extends('layouts.site-current', ['title' => 'Faculty & Staff', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('content')
<section class="nacs12-hero">
    <div class="nacs11-shell">
        <span class="nacs12-kicker">Our School Community</span>
        <h1>Faculty &amp; Staff</h1>
        <p>Meet the school leaders, teachers, and support staff whose published profiles have been approved for the public website.</p>
    </div>
</section>

<section class="nacs12-section">
    <div class="nacs11-shell">
        @if($departments->isNotEmpty())
        <div class="nacs12-filters" aria-label="Faculty departments">
            <a href="{{ route('faculty.index') }}" class="{{ $department === '' ? 'is-active' : '' }}">All</a>
            @foreach($departments as $item)
                <a href="{{ route('faculty.index', ['department' => $item]) }}" class="{{ $department === $item ? 'is-active' : '' }}">{{ $item }}</a>
            @endforeach
        </div>
        @endif

        <div class="nacs12-card-grid">
            @forelse($profiles as $profile)
                <article class="nacs12-person-card">
                    @if($profile->photo_path)
                        <img src="{{ Storage::url($profile->photo_path) }}" alt="{{ $profile->alt_text ?: $profile->name }}">
                    @else
                        <div class="nacs12-person-placeholder" aria-hidden="true">{{ strtoupper(substr($profile->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        @if($profile->department)<span class="nacs12-chip">{{ $profile->department }}</span>@endif
                        <h2>{{ $profile->name }}</h2>
                        <strong>{{ $profile->position }}</strong>
                        @if($profile->grade_subject)<p>{{ $profile->grade_subject }}</p>@endif
                        @if($profile->biography)<p>{{ $profile->biography }}</p>@endif
                        @if($profile->credentials)<small>{{ $profile->credentials }}</small>@endif
                    </div>
                </article>
            @empty
                <div class="nacs12-empty">No faculty or staff profiles are published yet.</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
