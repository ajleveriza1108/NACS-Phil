@extends('admin.layouts.app', ['title' => 'Events'])

@section('content')
<section class="cm-page-head">
    <div><span class="cm-eyebrow">School calendar</span><h1>Events</h1><p>Keep parents informed about orientations, programs, examinations, celebrations, and other school dates.</p></div>
    <a href="{{ route('admin.events.create') }}" class="cm-button cm-button--primary">+ Add Event</a>
</section>

<div class="cm-content-list">
@forelse($events as $event)
    <article class="cm-content-item">
        <div class="cm-date-tile"><small>{{ $event->starts_at->format('M') }}</small><strong>{{ $event->starts_at->format('d') }}</strong></div>
        <div class="cm-content-item__body">
            <small>{{ $event->published_at ? 'Published' : 'Draft' }} · {{ $event->starts_at->format('M j, Y g:i A') }}</small>
            <h2>{{ $event->title }}</h2>
            <p>{{ $event->venue ?: 'Venue not specified' }}</p>
        </div>
        <div class="cm-content-item__actions">
            <a href="{{ route('admin.events.edit', $event) }}">Edit</a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" data-cm-confirm="Delete this event?">
                @csrf @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
    </article>
@empty
    <div class="cm-empty cm-empty--large">No events yet. Click &ldquo;Add Event&rdquo; to create the first one.</div>
@endforelse
</div>
<div class="cm-pagination">{{ $events->links() }}</div>
@endsection