@php
    $admissionsContent = \App\Support\AdmissionsContent::normalize(
        \App\Models\SiteContent::valuesFor('admissions', \App\Support\AdmissionsContent::defaults())
    );
@endphp
@extends('layouts.site-current', ['bodyClass' => 'admissions-phase4 nacs-current-page nacs-current-page--admissions', 'mainId' => 'admissions-main', 'mainClass' => '', 'assetBundle' => 'admissions', 'useVite' => false, 'title' => 'Admissions', 'description' => 'Review NACS-Phil admissions information, requirements, and application steps.'])
@section('content')
<section class="admissions-hero">
    <div class="admissions-hero__grid" aria-hidden="true"></div>
    <div class="admissions-shell admissions-hero__inner">
        <div class="admissions-hero__copy" data-admissions-reveal>
            <span class="admissions-pill">{{ $admissionsContent['hero_badge'] }}</span>
            <h1>{{ $admissionsContent['hero_heading'] }} <span>{{ $admissionsContent['hero_highlight'] }}</span></h1>
            <p>{{ $admissionsContent['hero_lead'] }}</p>
            <div class="admissions-hero__actions">
                <a class="admissions-button admissions-button--primary" href="#admission-steps">See the Process <span aria-hidden="true">&darr;</span></a>
                <a class="admissions-button admissions-button--secondary" href="{{ route('contact') }}">Ask Admissions <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>

        <div class="admissions-hero__visual" data-admissions-reveal>
            <div class="admissions-hero__visual-frame">
                @if(!empty($admissionsContent['hero_image_path']))
                    <img src="{{ Storage::disk('public')->url($admissionsContent['hero_image_path']) }}" alt="NACS-Phil families and school community gathering together.">
                @else
                    <img src="{{ asset('assets/current/media/152a7db4d165-admissions-visual.svg') }}" alt="Abstract admissions journey illustration showing clear steps from inquiry to enrollment guidance.">
                @endif
                <div class="admissions-hero__status">
                    <span aria-hidden="true"></span>
                    <div><strong>{{ $admissionsContent['status_label'] }}</strong><small>{{ $admissionsContent['status_value'] }}</small></div>
                </div>
            </div>
        </div>
    </div>

    <div class="admissions-shell admissions-info-strip" data-admissions-reveal>
        <article><span>01</span><div><strong>Clear Process</strong><small>Step-by-step family guidance</small></div></article>
        <article><span>02</span><div><strong>Current Requirements</strong><small>School-approved information only</small></div></article>
        <article><span>03</span><div><strong>Family Support</strong><small>Questions welcomed before enrollment</small></div></article>
        <article><span>04</span><div><strong>Privacy-Aware</strong><small>Protect applicant information</small></div></article>
    </div>
</section>

<section class="admissions-section admissions-welcome">
    <div class="admissions-shell admissions-welcome__inner" data-admissions-reveal>
        <span class="admissions-kicker">Welcome to Admissions</span>
        <h2>{{ $admissionsContent['welcome_heading'] }}</h2>
        <p>{{ $admissionsContent['welcome_text'] }}</p>
    </div>
</section>

<section id="admission-steps" class="admissions-section admissions-section--soft">
    <div class="admissions-shell">
        <div class="admissions-section-head" data-admissions-reveal>
            <div><span class="admissions-kicker">How It Works</span><h2>Four steps families can follow with confidence.</h2></div>
            <p>The wording is editable by authorized staff while the step structure stays protected and consistent on every screen size.</p>
        </div>

        <div class="admissions-steps">
            @foreach([1,2,3,4] as $number)
                <article data-admissions-reveal>
                    <div class="admissions-steps__icon" aria-hidden="true">{{ $number }}</div>
                    <h3>{{ $admissionsContent['step_'.$number.'_title'] }}</h3>
                    <p>{{ $admissionsContent['step_'.$number.'_text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section id="requirements" class="admissions-section">
    <div class="admissions-shell">
        <div class="admissions-section-head" data-admissions-reveal>
            <div><span class="admissions-kicker">Prepare Carefully</span><h2>{{ $admissionsContent['requirements_heading'] }}</h2></div>
            <p>{{ $admissionsContent['requirements_intro'] }}</p>
        </div>

        <div class="admissions-requirements">
            @foreach([1,2,3,4] as $number)
                <article data-admissions-reveal>
                    <span>{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
                    <div><h3>{{ $admissionsContent['requirement_'.$number.'_title'] }}</h3><p>{{ $admissionsContent['requirement_'.$number.'_text'] }}</p></div>
                </article>
            @endforeach
        </div>

        <div class="admissions-note" data-admissions-reveal>
            <span aria-hidden="true">!</span>
            <p>{{ $admissionsContent['requirements_note'] }}</p>
        </div>
    </div>
</section>

<section class="admissions-dates">
    <div class="admissions-shell admissions-dates__grid">
        <div class="admissions-dates__copy" data-admissions-reveal>
            <span class="admissions-kicker admissions-kicker--light">Timing & Availability</span>
            <h2>{{ $admissionsContent['dates_heading'] }}</h2>
            <p>{{ $admissionsContent['dates_text'] }}</p>
        </div>
        <div class="admissions-dates__cards" data-admissions-reveal>
            <article><span>{{ $admissionsContent['school_year_label'] }}</span><strong>{{ $admissionsContent['school_year_value'] }}</strong></article>
            <article><span>{{ $admissionsContent['status_label'] }}</span><strong>{{ $admissionsContent['status_value'] }}</strong></article>
        </div>
    </div>
</section>

<section id="admissions-faq" class="admissions-section admissions-section--soft">
    <div class="admissions-shell admissions-faq">
        <div class="admissions-faq__heading" data-admissions-reveal>
            <span class="admissions-kicker">Questions Families Ask</span>
            <h2>{{ $admissionsContent['faq_heading'] }}</h2>
            <p>Tap or click a question to expand its answer.</p>
        </div>

        <div class="admissions-faq__items">
            @foreach([1,2,3,4] as $number)
                <details data-admissions-reveal>
                    <summary><span>{{ $admissionsContent['faq_'.$number.'_q'] }}</span><b aria-hidden="true">+</b></summary>
                    <div><p>{{ $admissionsContent['faq_'.$number.'_a'] }}</p></div>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section class="admissions-section admissions-privacy">
    <div class="admissions-shell admissions-privacy__card" data-admissions-reveal>
        <div class="admissions-privacy__icon" aria-hidden="true">✓</div>
        <div>
            <span class="admissions-kicker">Privacy Reminder</span>
            <h2>{{ $admissionsContent['privacy_heading'] }}</h2>
            <p>{{ $admissionsContent['privacy_text'] }}</p>
            <a href="{{ route('privacy') }}">Read the public privacy page <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>

<section class="admissions-section admissions-section--cta">
    <div class="admissions-shell">
        <div class="admissions-final-cta" data-admissions-reveal>
            <div>
                <span class="admissions-kicker admissions-kicker--gold">Next Step</span>
                <h2>{{ $admissionsContent['cta_heading'] }}</h2>
                <p>{{ $admissionsContent['cta_text'] }}</p>
            </div>
            <div class="admissions-final-cta__actions">
                <a class="admissions-button admissions-button--gold" href="{{ route('contact') }}">{{ $admissionsContent['cta_primary_button'] }} <span aria-hidden="true">&rarr;</span></a>
                <a class="admissions-button admissions-button--glass" href="{{ route('programs') }}">{{ $admissionsContent['cta_secondary_button'] }}</a>
            </div>
        </div>
    </div>
</section>

<section class="admissions-section admissions-section--soft">
    <div class="admissions-shell">
        <div class="admissions-final-cta" data-admissions-reveal>
            <div>
                <span class="admissions-kicker admissions-kicker--gold">Private Admissions Portal</span>
                <h2>Apply or check an existing preliminary application.</h2>
                <p>Families receive a private reference and access code. Final admission remains subject to official school review.</p>
            </div>
            <div class="admissions-final-cta__actions">
                <a class="admissions-button admissions-button--gold" href="{{ route('admissions.apply') }}">Start Application <span aria-hidden="true">&rarr;</span></a>
                <a class="admissions-button admissions-button--glass" href="{{ route('admissions.track') }}">Track Application</a>
            </div>
        </div>
    </div>
</section>
@endsection
