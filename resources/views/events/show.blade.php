@php($eventsContent = \App\Models\SiteContent::valuesFor('events', \App\Support\EventsContent::defaults()))
@extends('layouts.events-phase6')
@section('title',$event->title)
@section('meta_description',\Illuminate\Support\Str::limit($event->description,155))

@section('content')
<section class="event-detail-hero">
<div class="events-shell event-detail-hero__inner" data-events-reveal>
    <a class="events-back" href="{{ route('events.index') }}">&larr; {{ $eventsContent['detail_back_label'] }}</a>
    <div class="event-detail-meta">
        <span>{{ $event->starts_at->format('F j, Y') }}</span>
        @if($event->is_all_day)
            <span>All day</span>
        @else
            <span>{{ $event->starts_at->format('g:i A') }} â€“ {{ $event->ends_at->format('g:i A') }}</span>
        @endif
        @if($event->venue)<span>{{ $event->venue }}</span>@endif
    </div>
    <h1>{{ $event->title }}</h1>
</div>
</section>

<section class="events-section">
<div class="events-shell event-detail-grid">
    <article class="event-detail-content" data-events-reveal>
        <div class="event-detail-copy">{!! nl2br(e($event->description)) !!}</div>
        @if($event->registration_url)
            <a class="events-button events-button--primary" href="{{ $event->registration_url }}" target="_blank" rel="noopener noreferrer">Open Registration &nearr;</a>
        @endif
    </article>
    <aside class="event-detail-aside" data-events-reveal>
        <div><span>Date</span><strong>{{ $event->starts_at->format('F j, Y') }}</strong></div>
        <div><span>Time</span><strong>{{ $event->is_all_day ? 'All day' : $event->starts_at->format('g:i A').' â€“ '.$event->ends_at->format('g:i A') }}</strong></div>
        <div><span>Venue</span><strong>{{ $event->venue ?: 'Contact the school' }}</strong></div>
    </aside>
</div>
</section>

<section class="events-section events-section--cta">
<div class="events-shell">
<div class="events-final-cta" data-events-reveal>
    <div><span class="events-kicker events-kicker--gold">Event Information</span><h2>{{ $eventsContent['detail_contact_heading'] }}</h2><p>{{ $eventsContent['detail_contact_text'] }}</p></div>
    <a class="events-button events-button--gold" href="{{ route('contact') }}">{{ $eventsContent['detail_contact_button'] }} &rarr;</a>
</div>
</div>
</section>
@endsection