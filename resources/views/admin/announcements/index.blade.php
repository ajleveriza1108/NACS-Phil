@extends('admin.layouts.app', ['title' => 'Announcements'])

@section('content')
<section class="cm-page-head">
    <div><span class="cm-eyebrow">Daily posting</span><h1>Announcements</h1><p>Post school news, reminders, enrollment notices, and urgent updates.</p></div>
    <a href="{{ route('admin.announcements.create') }}" class="cm-button cm-button--primary">+ Post Announcement</a>
</section>

<div class="cm-content-list">
@forelse($announcements as $announcement)
    <article class="cm-content-item">
        <div class="cm-content-item__status">
            <span @class(['is-live' => filled($announcement->published_at)])>{{ $announcement->published_at ? 'Published' : 'Draft' }}</span>
            @if($announcement->is_featured)<b>Homepage</b>@endif
        </div>
        <div class="cm-content-item__body">
            <small>{{ ucfirst($announcement->type) }} · Updated {{ $announcement->updated_at->diffForHumans() }}</small>
            <h2>{{ $announcement->title }}</h2>
            @if($announcement->excerpt)<p>{{ $announcement->excerpt }}</p>@endif
        </div>
        <div class="cm-content-item__actions">
            <a href="{{ route('admin.announcements.edit', $announcement) }}">Edit</a>
            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" data-cm-confirm="Delete this announcement? This action uses the current website delete behavior.">
                @csrf @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        </div>
    </article>
@empty
    <div class="cm-empty cm-empty--large">No announcements yet. Click â€œPost Announcementâ€ to create the first one.</div>
@endforelse
</div>
<div class="cm-pagination">{{ $announcements->links() }}</div>
@endsection