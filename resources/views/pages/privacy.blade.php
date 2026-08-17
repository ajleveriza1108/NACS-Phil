@extends('layouts.site-current', ['title' => 'Privacy and Child Protection', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('content')
<section class="nacs11-privacy-hero">
    <div class="nacs11-shell nacs11-privacy-hero__inner">
        <span class="nacs11-kicker">Privacy &amp; Child Protection</span>
        <h1>Protecting children is part of faithful stewardship.</h1>
        <p>This development notice must be reviewed by the school and qualified Philippine privacy counsel before public launch.</p>
    </div>
</section>

<section class="nacs11-privacy-section">
    <div class="nacs11-shell nacs11-privacy-grid">
        <article class="nacs11-privacy-card">
            <span aria-hidden="true">01</span>
            <h2>Basic inquiry data</h2>
            <p>The public inquiry form collects a parent or guardian name, at least one contact method, an optional learner name, the program of interest, and a message. The information is used only to answer the inquiry and support admissions communication.</p>
        </article>

        <article class="nacs11-privacy-card">
            <span aria-hidden="true">02</span>
            <h2>Photographs and school media</h2>
            <p>The gallery is designed so an image cannot appear publicly unless an administrator enables publication and records that consent and image rights were checked. The school should retain the underlying authorization records outside the public website.</p>
        </article>

        <article class="nacs11-privacy-card">
            <span aria-hidden="true">03</span>
            <h2>Information that does not belong on public pages</h2>
            <p>Student numbers, complete birth dates, home addresses, grades, medical details, parent contact lists, transportation routines, disciplinary information, and private documents must not be published.</p>
        </article>

        <article class="nacs11-privacy-card">
            <span aria-hidden="true">04</span>
            <h2>Retention and requests</h2>
            <p>The school must approve a retention schedule and a clear process for families to request access, correction, or appropriate deletion of their information.</p>
        </article>

        <article class="nacs11-privacy-card nacs11-privacy-card--external">
            <span aria-hidden="true">05</span>
            <h2>Facebook-hosted videos and livestreams</h2>
            <p>The Live &amp; Videos page can display public Facebook video or Facebook Live players. NACS-Phil stores the school-entered Facebook link and related page metadata, but the actual video remains hosted and streamed by Facebook. When an embedded Facebook player loads, the visitor's browser connects to Facebook, and Meta/Facebook privacy and cookie practices may apply.</p>
        </article>

        <article class="nacs11-privacy-card nacs11-privacy-card--external">
            <span aria-hidden="true">06</span>
            <h2>Adaptive anti-bot verification</h2>
            <p>Protected forms may use Cloudflare Turnstile to distinguish normal visitors from automated abuse. The verification is designed to run automatically when possible and request human interaction only when needed. When enabled on the live website, the visitor's browser connects to Cloudflare for this anti-bot check, and Cloudflare's applicable privacy practices may apply.</p>
        </article>

        <article class="nacs11-privacy-card nacs11-privacy-card--wide">
            <span aria-hidden="true">!</span>
            <h2>Admissions documents stay private</h2>
            <p>The preliminary admissions portal stores requested documents outside the public website. Families should upload sensitive documents only after an authorized school reviewer specifically requests them through the admissions workflow.</p>
            <div class="nacs11-privacy-note">Public-facing privacy wording is a school policy/legal review item. This page describes the safeguards currently built into the development website; it is not a substitute for the school's final privacy notice.</div>
        </article>
    </div>
</section>
@endsection
