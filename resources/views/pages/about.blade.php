@php($aboutContent = \App\Models\SiteContent::valuesFor('about', \App\Support\AboutContent::defaults()))
@extends('layouts.about-phase2')

@section('content')
<section class="about-hero">
    <div class="about-hero__grid-lines" aria-hidden="true"></div>
    <div class="about-shell about-hero__inner">
        <div class="about-hero__copy" data-about-reveal>
            <span class="about-pill">{{ $aboutContent['hero_badge'] }}</span>
            <h1>{{ $aboutContent['hero_heading'] }} <span>{{ $aboutContent['hero_highlight'] }}</span></h1>
            <p>{{ $aboutContent['hero_lead'] }}</p>
            <div class="about-hero__actions">
                <a class="about-button about-button--primary" href="#about-story">Our History <span aria-hidden="true">&darr;</span></a>
                <a class="about-button about-button--secondary" href="{{ route('contact') }}">Contact the School <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
        <div class="about-hero__visual" data-about-reveal>
            <div class="about-hero__visual-frame">
                @if(!empty($aboutContent['hero_image_path']))
                    <img src="{{ Storage::disk('public')->url($aboutContent['hero_image_path']) }}" alt="NACS-Phil school community and educators.">
                @else
                    <img src="{{ asset('assets/phase2-about/about-visual.svg') }}" alt="Abstract concept illustration representing Christian education, learning, community, and growth at NACS-Phil.">
                @endif
                <div class="about-hero__visual-label"><i aria-hidden="true"></i><span>Faith · Learning · Community</span></div>
            </div>
        </div>
    </div>

    <div class="about-shell about-identity-strip" data-about-reveal>
        <article><span>01</span><div><strong>Christ-Centered</strong><small>Faith integrated with learning and character</small></div></article>
        <article><span>02</span><div><strong>Preschool</strong><small>A caring beginning for young learners</small></div></article>
        <article><span>03</span><div><strong>Elementary</strong><small>Strong foundations for lifelong learning</small></div></article>
        <article><span>04</span><div><strong>Junior High</strong><small>Preparing responsible future leaders</small></div></article>
    </div>
</section>

<section id="about-story" class="about-section">
    <div class="about-shell about-story">
        <div class="about-story__copy" data-about-reveal>
            <span class="about-kicker">{{ $aboutContent['story_kicker'] }}</span>
            <h2>{{ $aboutContent['story_heading'] }}</h2>
            <div class="about-rich-text">{!! nl2br(e($aboutContent['story_body'])) !!}</div>
            <div class="about-approval-note"><span aria-hidden="true">✓</span><p>{{ $aboutContent['story_note'] }}</p></div>
        </div>
        <aside class="about-story__visual" data-about-reveal>
            <div class="about-story__cross" aria-hidden="true"><span></span><i></i></div>
            <div class="about-story__card">
                <span>School identity</span><strong>{{ config('nacs.short_name') }}</strong>
                <p>Noel Academy Christian of Sariaya Philippines</p><div></div><small>{{ config('nacs.address') }}</small>
            </div>
        </aside>
    </div>
</section>

<section class="about-section about-section--soft">
    <div class="about-shell">
        <div class="about-section-head" data-about-reveal>
            <div><span class="about-kicker">Purpose & Direction</span><h2>Mission and vision presented clearly for every family.</h2></div>
            <p>Authorized staff can update the exact approved wording without changing the page design.</p>
        </div>
        <div class="about-purpose-grid">
            <article class="about-purpose-card" data-about-reveal>
                <span class="about-purpose-card__label">01 · Mission</span><div class="about-purpose-card__icon" aria-hidden="true">+</div>
                <h3>{{ $aboutContent['mission_title'] }}</h3><p>{{ $aboutContent['mission_text'] }}</p>
            </article>
            <article class="about-purpose-card about-purpose-card--vision" data-about-reveal>
                <span class="about-purpose-card__label">02 · Vision</span><div class="about-purpose-card__icon" aria-hidden="true">&odot;</div>
                <h3>{{ $aboutContent['vision_title'] }}</h3><p>{{ $aboutContent['vision_text'] }}</p>
            </article>
        </div>
    </div>
</section>

<section class="about-faith">
    <div class="about-shell about-faith__grid">
        <div class="about-faith__copy" data-about-reveal>
            <span class="about-kicker about-kicker--light">{{ $aboutContent['faith_kicker'] }}</span>
            <h2>{{ $aboutContent['faith_heading'] }}</h2><p>{{ $aboutContent['faith_text'] }}</p>
        </div>
        <blockquote class="about-verse" data-about-reveal>
            <span class="about-verse__quote" aria-hidden="true">&ldquo;</span>
            <p>{{ $aboutContent['verse_text'] }}</p><cite>{{ $aboutContent['verse_reference'] }}</cite>
        </blockquote>
    </div>
</section>

<section class="about-section">
    <div class="about-shell">
        <div class="about-section-head about-section-head--single" data-about-reveal>
            <div><span class="about-kicker">Core Values</span><h2>{{ $aboutContent['values_heading'] }}</h2></div>
        </div>
        <div class="about-values">
            @foreach([1,2,3,4] as $number)
                <article class="about-value-card" data-about-reveal>
                    <span class="about-value-card__number">0{{ $number }}</span>
                    <span class="about-value-card__symbol" aria-hidden="true">@if($number === 1)+@else{{ $number }}@endif</span>
                    <h3>{{ $aboutContent['value_'.$number.'_title'] }}</h3>
                    <p>{{ $aboutContent['value_'.$number.'_text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="about-distinctives">
    <div class="about-shell">
        <div class="about-distinctives__head" data-about-reveal>
            <span class="about-kicker">{{ $aboutContent['distinctives_kicker'] }}</span>
            <h2>{{ $aboutContent['distinctives_heading'] }}</h2>
            <p>{{ $aboutContent['distinctives_intro'] }}</p>
        </div>
        <div class="about-distinctives__grid">
            @foreach([1,2,3,4] as $number)
                <article class="about-distinctive" data-about-reveal>
                    <span class="about-distinctive__icon" aria-hidden="true">0{{ $number }}</span>
                    <h3>{{ $aboutContent['distinctive_'.$number.'_title'] }}</h3>
                    <p>{{ $aboutContent['distinctive_'.$number.'_text'] }}</p>
                </article>
            @endforeach
        </div>
        <p class="about-distinctives__note">{{ $aboutContent['distinctives_note'] }}</p>
    </div>
</section>
<section class="about-section about-section--leadership">
    <div class="about-shell about-leadership">
        <div class="about-leadership__portrait" data-about-reveal>
            <div class="about-leadership__portrait-inner">
                @if(!empty($aboutContent['leadership_image_path']))
                    <img src="{{ Storage::disk('public')->url($aboutContent['leadership_image_path']) }}" alt="NACS-Phil educators during professional development." style="width:100%;height:100%;object-fit:cover;">
                @else
                    <span aria-hidden="true">N</span><small>Approved leadership portrait can be added through the About page editor.</small>
                @endif
            </div>
        </div>
        <div class="about-leadership__copy" data-about-reveal>
            <span class="about-kicker">{{ $aboutContent['leadership_kicker'] }}</span><h2>{{ $aboutContent['leadership_heading'] }}</h2>
            <div class="about-rich-text">{!! nl2br(e($aboutContent['leader_message'])) !!}</div>
            <div class="about-signature">@if(!empty($aboutContent['leader_name']))<strong>{{ $aboutContent['leader_name'] }}</strong>@endif<span>{{ $aboutContent['leader_role'] }}</span></div>
        </div>
    </div>
</section>

<section class="about-community">
    <div class="about-shell about-community__inner">
        <div data-about-reveal><span class="about-kicker">One School Community</span><h2>{{ $aboutContent['community_heading'] }}</h2><p>{{ $aboutContent['community_text'] }}</p></div>
        <div class="about-community__levels" data-about-reveal>
            <a href="{{ route('programs') }}"><span>Early Years</span><strong>Preschool</strong><b>&rarr;</b></a>
            <a href="{{ route('programs') }}"><span>Foundation Years</span><strong>Elementary</strong><b>&rarr;</b></a>
            <a href="{{ route('programs') }}"><span>Leadership Years</span><strong>Junior High</strong><b>&rarr;</b></a>
        </div>
    </div>
</section>

<section class="about-section about-section--cta">
    <div class="about-shell">
        <div class="about-final-cta" data-about-reveal>
            <div><span class="about-kicker about-kicker--gold">Continue Exploring</span><h2>{{ $aboutContent['cta_heading'] }}</h2><p>{{ $aboutContent['cta_text'] }}</p></div>
            <div class="about-final-cta__actions">
                <a class="about-button about-button--gold" href="{{ route('programs') }}">{{ $aboutContent['cta_programs_button'] }} <span aria-hidden="true">&rarr;</span></a>
                <a class="about-button about-button--glass" href="{{ route('contact') }}">{{ $aboutContent['cta_contact_button'] }}</a>
            </div>
        </div>
    </div>
</section>
@endsection
