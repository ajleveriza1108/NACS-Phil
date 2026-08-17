@php($programsContent = \App\Models\SiteContent::valuesFor('programs', \App\Support\ProgramsContent::defaults()))
@extends('layouts.site-current', ['bodyClass' => 'programs-phase3 nacs-current-page nacs-current-page--programs', 'mainId' => 'programs-main', 'mainClass' => '', 'assetBundle' => 'programs', 'useVite' => false, 'title' => 'Programs', 'description' => 'Explore Preschool, Elementary, and Junior High programs at NACS-Phil.'])
@section('content')
<section class="programs-hero">
    <div class="programs-hero__grid" aria-hidden="true"></div>
    <div class="programs-shell programs-hero__inner">
        <div class="programs-hero__copy" data-programs-reveal>
            <span class="programs-pill">{{ $programsContent['hero_badge'] }}</span>
            <h1>{{ $programsContent['hero_heading'] }} <span>{{ $programsContent['hero_highlight'] }}</span></h1>
            <p>{{ $programsContent['hero_lead'] }}</p>
            <div class="programs-hero__actions">
                <a class="programs-button programs-button--primary" href="#program-pathways">Explore Pathways <span aria-hidden="true">&darr;</span></a>
                <a class="programs-button programs-button--secondary" href="{{ route('admissions') }}">View Admissions <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>

        <div class="programs-hero__visual" data-programs-reveal>
            <div class="programs-hero__visual-frame">
                @if(!empty($programsContent['hero_image_path']))
                    <img src="{{ Storage::disk('public')->url($programsContent['hero_image_path']) }}" alt="NACS-Phil students learning together across school programs.">
                @else
                    <img src="{{ asset('assets/current/media/b6accc489651-programs-visual.svg') }}" alt="Abstract illustration showing three connected learning stages representing Preschool, Elementary, and Junior High at NACS-Phil.">
                @endif
                <div class="programs-hero__badge"><span>3</span><div><strong>Learning Stages</strong><small>One connected school journey</small></div></div>
            </div>
        </div>
    </div>

    <div class="programs-shell programs-stage-strip" data-programs-reveal>
        <a href="#preschool"><span>01</span><div><strong>{{ $programsContent['preschool_title'] }}</strong><small>{{ $programsContent['preschool_levels'] }}</small></div><b>&rarr;</b></a>
        <a href="#elementary"><span>02</span><div><strong>{{ $programsContent['elementary_title'] }}</strong><small>{{ $programsContent['elementary_levels'] }}</small></div><b>&rarr;</b></a>
        <a href="#junior-high"><span>03</span><div><strong>{{ $programsContent['junior_title'] }}</strong><small>{{ $programsContent['junior_levels'] }}</small></div><b>&rarr;</b></a>
    </div>
</section>

<section id="program-pathways" class="programs-section programs-overview">
    <div class="programs-shell programs-overview__inner" data-programs-reveal>
        <span class="programs-kicker">Learning Pathways</span>
        <h2>{{ $programsContent['overview_heading'] }}</h2>
        <p>{{ $programsContent['overview_text'] }}</p>
    </div>
</section>

@foreach([
    'preschool' => ['preschool', '01', 'programs-program--preschool'],
    'elementary' => ['elementary', '02', 'programs-program--elementary'],
    'junior' => ['junior-high', '03', 'programs-program--junior'],
] as $prefix => $meta)
<section id="{{ $meta[0] }}" class="programs-program {{ $meta[2] }}">
    <div class="programs-shell programs-program__grid">
        <div class="programs-program__visual" data-programs-reveal>
            @if(!empty($programsContent[$prefix.'_image_path']))
                <img src="{{ Storage::disk('public')->url($programsContent[$prefix.'_image_path']) }}" alt="{{ $programsContent[$prefix.'_title'] }} learning at NACS-Phil." style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">
                <div aria-hidden="true" style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(3,23,47,.08),rgba(3,23,47,.28));"></div>
            @endif
            <div class="programs-program__number">{{ $meta[1] }}</div>
            @if(empty($programsContent[$prefix.'_image_path']))
                <div class="programs-program__symbol" aria-hidden="true">
                    @if($prefix === 'preschool')
                        <span>ABC</span>
                    @elseif($prefix === 'elementary')
                        <span>123</span>
                    @else
                        <span>&infin;</span>
                    @endif
                </div>
            @endif
            <div class="programs-program__level">{{ $programsContent[$prefix.'_levels'] }}</div>
        </div>

        <div class="programs-program__copy" data-programs-reveal>
            <span class="programs-kicker">{{ $programsContent[$prefix.'_kicker'] }}</span>
            <h2>{{ $programsContent[$prefix.'_title'] }}</h2>
            <span class="programs-level-pill">{{ $programsContent[$prefix.'_levels'] }}</span>
            <p>{{ $programsContent[$prefix.'_text'] }}</p>

            <div class="programs-feature-grid">
                @foreach([1,2,3,4] as $number)
                    <div><span aria-hidden="true">✓</span><strong>{{ $programsContent[$prefix.'_feature_'.$number] }}</strong></div>
                @endforeach
            </div>

            <a class="programs-inline-link" href="{{ route('admissions') }}">Ask about {{ $programsContent[$prefix.'_title'] }} admissions <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>
@endforeach

<section class="programs-section programs-section--soft">
    <div class="programs-shell">
        <div class="programs-section-head" data-programs-reveal>
            <div><span class="programs-kicker">{{ $programsContent['approach_kicker'] }}</span><h2>{{ $programsContent['approach_heading'] }}</h2></div>
            <p>{{ $programsContent['approach_text'] }}</p>
        </div>

        <div class="programs-approach-grid">
            @foreach([1,2,3] as $number)
                <article data-programs-reveal>
                    <span class="programs-approach-grid__number">0{{ $number }}</span>
                    <div class="programs-approach-grid__icon" aria-hidden="true">{{ $number === 1 ? 'K' : ($number === 2 ? 'G' : 'S') }}</div>
                    <h3>{{ $programsContent['approach_'.$number.'_title'] }}</h3>
                    <p>{{ $programsContent['approach_'.$number.'_text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="programs-faith">
    <div class="programs-shell programs-faith__grid">
        <div class="programs-faith__copy" data-programs-reveal>
            <span class="programs-kicker programs-kicker--light">Christ-Centered Learning</span>
            <h2>{{ $programsContent['faith_heading'] }}</h2>
            <p>{{ $programsContent['faith_text'] }}</p>
        </div>
        <blockquote class="programs-verse" data-programs-reveal>
            <span aria-hidden="true">&ldquo;</span>
            <p>{{ $programsContent['verse_text'] }}</p>
            <cite>{{ $programsContent['verse_reference'] }}</cite>
        </blockquote>
    </div>
</section>

<section class="programs-section programs-section--cta">
    <div class="programs-shell">
        <div class="programs-final-cta" data-programs-reveal>
            <div>
                <span class="programs-kicker programs-kicker--gold">Next Step</span>
                <h2>{{ $programsContent['cta_heading'] }}</h2>
                <p>{{ $programsContent['cta_text'] }}</p>
            </div>
            <div class="programs-final-cta__actions">
                <a class="programs-button programs-button--gold" href="{{ route('admissions') }}">{{ $programsContent['cta_admissions_button'] }} <span aria-hidden="true">&rarr;</span></a>
                <a class="programs-button programs-button--glass" href="{{ route('contact') }}">{{ $programsContent['cta_contact_button'] }}</a>
            </div>
        </div>
    </div>
</section>
@endsection
