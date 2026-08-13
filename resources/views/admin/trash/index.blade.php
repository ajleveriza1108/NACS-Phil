@extends('admin.layouts.app', ['title' => 'Trash'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Recovery</span>
        <h1>Safe Trash</h1>
        <p>Deleted announcements, events, and photos stay recoverable here. Only the Super Admin can permanently delete them.</p>
    </div>
</section>

@php
$groups = [
    ['title'=>'Announcements','type'=>'announcement','items'=>$announcements],
    ['title'=>'Events','type'=>'event','items'=>$events],
    ['title'=>'Photos','type'=>'photo','items'=>$photos],
];
@endphp

@foreach($groups as $group)
<section class="cm-panel cm-panel--wide p9b-trash-panel">
    <div class="cm-panel__head">
        <div><span class="cm-eyebrow">Trash</span><h2>{{ $group['title'] }}</h2></div>
        <span>{{ $group['items']->count() }} item(s)</span>
    </div>

    <div class="p9b-trash-list">
        @forelse($group['items'] as $item)
            <article>
                <div>
                    <strong>{{ $item->title }}</strong>
                    <small>Deleted {{ $item->deleted_at?->diffForHumans() }}</small>
                </div>
                <div class="p9b-trash-actions">
                    <form method="POST" action="{{ route('admin.trash.restore',['type'=>$group['type'],'id'=>$item->id]) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="cm-button cm-button--secondary">Restore</button>
                    </form>

                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('admin.trash.destroy',['type'=>$group['type'],'id'=>$item->id]) }}" onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p9b-danger-button">Delete Forever</button>
                    </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="cm-empty">Trash is empty.</div>
        @endforelse
    </div>
</section>
@endforeach
@endsection