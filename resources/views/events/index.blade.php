@php($eventsContent = \App\Models\SiteContent::valuesFor('events', \App\Support\EventsContent::defaults()))
@extends('layouts.events-phase6')
@section('title','Events')

@section('content')
<section class="events-hero">
<div class="events-hero__grid"></div>
<div class="events-shell events-hero__inner">
    <div data-events-reveal>
        <span class="events-pill">{{ $eventsContent['hero_badge'] }}</span>
        <h1>{{ $eventsContent['hero_heading'] }} <span>{{ $eventsContent['hero_highlight'] }}</span></h1>
        <p>{{ $eventsContent['hero_lead'] }}</p>
        <div class="events-actions"><a class="events-button events-button--primary" href="#upcoming-events">View Upcoming Events &darr;</a><a class="events-button events-button--secondary" href="{{ route('contact') }}">Ask the School &rarr;</a></div>
    </div>
    <div class="events-visual" data-events-reveal><img src="{{ asset('assets/phase6-events/events-visual.svg') }}" alt="Abstract school calendar and community event illustration."></div>
</div>
</section>

<section id="upcoming-events" class="events-section">
<div class="events-shell">
    <div class="events-section-head" data-events-reveal><div><span class="events-kicker">School Calendar</span><h2>{{ $eventsContent['listing_heading'] }}</h2></div><p>{{ $eventsContent['listing_text'] }}</p></div>

    <div class="events-grid">
    @forelse($upcomingEvents as $event)
        <article class="event-card" data-events-reveal>
            <div class="event-card__date">
                <span>{{ $event->starts_at->format('M') }}</span>
                <strong>{{ $event->starts_at->format('d') }}</strong>
                <small>{{ $event->starts_at->format('Y') }}</small>
            </div>
            <div class="event-card__body">
                <div class="event-card__meta">
                    @if($event->is_all_day)
                        <span>All day</span>
                    @else
                        <span>{{ $event->starts_at->format('g:i A') }} &ndash; {{ $event->ends_at->format('g:i A') }}</span>
                    @endif
                    @if($event->venue)<span>{{ $event->venue }}</span>@endif
                </div>
                <h3><a href="{{ route('events.show', ['event' => $event->slug]) }}">{{ $event->title }}</a></h3>
                <p>{{ \Illuminate\Support\Str::limit($event->description, 155) }}</p>
                <div class="event-card__links">
                    <a href="{{ route('events.show', ['event' => $event->slug]) }}">View event &rarr;</a>
                    @if($event->registration_url)
                        <a href="{{ $event->registration_url }}" target="_blank" rel="noopener noreferrer">Registration &nearr;</a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="events-empty" data-events-reveal><span>E</span><h3>{{ $eventsContent['empty_heading'] }}</h3><p>{{ $eventsContent['empty_text'] }}</p></div>
    @endforelse
    </div>

    <div class="events-pagination">{{ $upcomingEvents->links() }}</div>
</div>
</section>
@endsection