@extends('admin.layouts.app', ['title' => 'Launch Readiness'])

@section('content')
<section class="p15-head">
    <div>
        <span class="cm-eyebrow">Phase 15 release gate</span>
        <h1>Launch Readiness</h1>
        <p>This page separates school-content approval from hosting configuration so the site is not declared ready simply because the code works.</p>
    </div>
    <div class="p15-score {{ $score === 100 ? 'is-ready' : '' }}" aria-label="Automatic readiness score">
        <strong>{{ $score }}%</strong>
        <span>{{ $passed }} of {{ $total }} automatic school/security checks passed</span>
    </div>
</section>

<div class="p15-notice">
    <strong>Local development is allowed to show production checks as pending.</strong>
    APP_ENV, APP_DEBUG, HTTPS, domain, and production credentials are completed on the hosting server, not on this local Windows project.
</div>

<section class="p15-grid">
    <article class="p15-panel">
        <div class="p15-panel-head">
            <div><span class="cm-eyebrow">Official information</span><h2>School approval</h2></div>
            <a href="{{ route('admin.settings.edit') }}">Open School Settings</a>
        </div>
        @include('admin.partials.launch-checks', ['checks' => $schoolChecks])
    </article>

    <article class="p15-panel">
        <div class="p15-panel-head">
            <div><span class="cm-eyebrow">Public website</span><h2>Content &amp; consent</h2></div>
            <span>{{ $publishedDocuments }} public document(s)</span>
        </div>
        @include('admin.partials.launch-checks', ['checks' => $contentChecks])
    </article>

    <article class="p15-panel">
        <div class="p15-panel-head">
            <div><span class="cm-eyebrow">Staff protection</span><h2>Security</h2></div>
            <a href="{{ route('admin.security.index') }}">My Security</a>
        </div>
        @include('admin.partials.launch-checks', ['checks' => $securityChecks])
    </article>

    <article class="p15-panel">
        <div class="p15-panel-head">
            <div><span class="cm-eyebrow">Hosting-only</span><h2>Production environment</h2></div>
            <span>Completed during deployment</span>
        </div>
        @include('admin.partials.launch-checks', ['checks' => $deploymentChecks])
    </article>
</section>

<section class="p15-panel p15-manual">
    <div class="p15-panel-head">
        <div><span class="cm-eyebrow">Human review required</span><h2>Final school sign-off</h2></div>
    </div>
    <p>These items cannot be truthfully approved by an automated test. A Principal/Super Admin should verify them before the website goes public.</p>
    <ol>
        @foreach($manualChecks as $check)
            <li><span>{{ $loop->iteration }}</span><p>{{ $check }}</p></li>
        @endforeach
    </ol>
</section>

<section class="p15-launch-rule">
    <strong>Launch rule</strong>
    <p>Do not treat a 100% automatic score as final approval. Public launch requires the automatic school/security checks, the manual sign-off above, and the production-hosting checks to be complete.</p>
</section>
@endsection
