@php($contactContent = \App\Models\SiteContent::valuesFor('contact', \App\Support\ContactContent::defaults()))
@extends('layouts.contact-phase8')
@section('title','Contact')

@section('content')
<section class="contact-hero">
<div class="contact-hero__grid"></div>
<div class="contact-shell contact-hero__inner">
    <div data-contact-reveal>
        <span class="contact-pill">{{ $contactContent['hero_badge'] }}</span>
        <h1>{{ $contactContent['hero_heading'] }} <span>{{ $contactContent['hero_highlight'] }}</span></h1>
        <p>{{ $contactContent['hero_lead'] }}</p>
        <div class="contact-actions"><a class="contact-button contact-button--primary" href="#inquiry">Send an Inquiry &darr;</a><a class="contact-button contact-button--secondary" href="#school-info">School Information &rarr;</a></div>
    </div>
    <div class="contact-visual" data-contact-reveal><img src="{{ asset('assets/phase8-contact/contact-visual.svg') }}" alt="Abstract illustration of a school office message and contact card."></div>
</div>
</section>

<section class="contact-section">
<div class="contact-shell contact-main-grid">
    <aside id="school-info" class="contact-info" data-contact-reveal>
        <span class="contact-kicker">School Office</span>
        <h2>{{ $contactContent['office_heading'] }}</h2>
        <p class="contact-info__lead">{{ $contactContent['office_text'] }}</p>

        <dl class="contact-details">
            <div><dt>Address</dt><dd>{{ $contactContent['address'] }}</dd></div>
            <div><dt>Phone</dt><dd>
                @if($contactContent['phone'])
                    <a href="tel:{{ preg_replace('/[^0-9+]/','',$contactContent['phone']) }}">{{ $contactContent['phone'] }}</a>
                @else
                    <span>Pending official verification</span>
                @endif
            </dd></div>
            <div><dt>Email</dt><dd>
                @if($contactContent['email'])
                    <a href="mailto:{{ $contactContent['email'] }}">{{ $contactContent['email'] }}</a>
                @else
                    <span>Pending official verification</span>
                @endif
            </dd></div>
            @if($contactContent['office_hours'])
                <div><dt>Office hours</dt><dd>{{ $contactContent['office_hours'] }}</dd></div>
            @endif
        </dl>

        @if($contactContent['facebook_url'])
            <a class="contact-social-link" href="{{ $contactContent['facebook_url'] }}" target="_blank" rel="noopener noreferrer">Open Facebook Page &nearr;</a>
        @endif

        <div class="contact-privacy-card">
            <span>Privacy</span>
            <h3>{{ $contactContent['privacy_heading'] }}</h3>
            <p>{{ $contactContent['privacy_text'] }}</p>
            <a href="{{ route('privacy') }}">Read privacy information &rarr;</a>
        </div>
    </aside>

    <section id="inquiry" class="contact-form-card" data-contact-reveal>
        <span class="contact-kicker">General Inquiry</span>
        <h2>{{ $contactContent['inquiry_heading'] }}</h2>
        <p class="contact-form-card__lead">{{ $contactContent['inquiry_text'] }}</p>

        @if(session('success'))
            <div class="contact-alert contact-alert--success" role="status">
                <strong>Inquiry received.</strong>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="contact-alert contact-alert--error" role="alert">
                <strong>Please check the highlighted information.</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inquiries.store') }}" class="contact-form">
            @csrf

            <div class="contact-field">
                <label for="guardian_name">Parent / guardian name <b>*</b></label>
                <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required maxlength="100" autocomplete="name">
            </div>

            <div class="contact-field">
                <label for="student_name">Student name <span>Optional</span></label>
                <input id="student_name" name="student_name" value="{{ old('student_name') }}" maxlength="100" autocomplete="off">
            </div>

            <div class="contact-field">
                <label for="email">Email <span>Email or phone required</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="150" autocomplete="email">
            </div>

            <div class="contact-field">
                <label for="phone">Phone <span>Email or phone required</span></label>
                <input id="phone" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
            </div>

            <div class="contact-field contact-field--full">
                <label for="level_interested">Program of interest <b>*</b></label>
                <select id="level_interested" name="level_interested" required>
                    <option value="">Choose one</option>
                    @foreach(['Preschool','Elementary','Junior High School','General Inquiry'] as $level)
                        <option value="{{ $level }}" @selected(old('level_interested') === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>

            <div class="contact-field contact-field--full">
                <div class="contact-label-row"><label for="message">Question or message <b>*</b></label><span data-message-count>0 / 2000</span></div>
                <textarea id="message" name="message" rows="7" required maxlength="2000" data-contact-message>{{ old('message') }}</textarea>
            </div>

            <div class="contact-honeypot" aria-hidden="true">
                <label for="website">Website</label>
                <input id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label class="contact-consent">
                <input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent'))>
                <span>I consent to the school using the information above to respond to this inquiry. I will not submit sensitive student documents through this form. Read the <a href="{{ route('privacy') }}">privacy notice</a>.</span>
            </label>

            <div class="contact-submit-row">
                <button type="submit" class="contact-button contact-button--primary">Send Inquiry <span aria-hidden="true">&rarr;</span></button>
                <small>Your message is stored in the school's inquiry system for authorized staff review.</small>
            </div>
        </form>
    </section>
</div>
</section>
@endsection