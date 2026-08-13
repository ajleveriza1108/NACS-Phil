@extends('admin.layouts.app', ['title' => 'Audit History'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Accountability</span>
        <h1>Audit History</h1>
        <p>Review recorded website-content actions, including edits, Trash moves, restores, and permanent deletions.</p>
    </div>
</section>

<nav class="p9b-audit-filters" aria-label="Audit action filter">
    <a href="{{ route('admin.audit.index') }}" @class(['is-active'=>$action===''])>All</a>
    @foreach(['created','updated','trashed','restored','permanently_deleted'] as $filter)
        <a href="{{ route('admin.audit.index',['action'=>$filter]) }}" @class(['is-active'=>$action===$filter])>{{ str_replace('_',' ',ucfirst($filter)) }}</a>
    @endforeach
</nav>

<section class="cm-panel cm-panel--wide">
    <div class="p9b-audit-list">
        @forelse($audits as $audit)
            <article>
                <span class="p9b-audit-action p9b-audit-action--{{ $audit->action }}">{{ str_replace('_',' ',$audit->action) }}</span>
                <div>
                    <strong>{{ $audit->label }}</strong>
                    <small>{{ class_basename($audit->auditable_type) }}{{ $audit->auditable_id ? ' #'.$audit->auditable_id : '' }}</small>
                </div>
                <div>
                    <strong>{{ $audit->actor_name ?: 'System / public action' }}</strong>
                    <small>{{ $audit->actor_role ?: 'No staff role' }}</small>
                </div>
                <time datetime="{{ $audit->created_at?->toIso8601String() }}">{{ $audit->created_at?->format('M j, Y g:i A') }}</time>
            </article>
        @empty
            <div class="cm-empty">No audit records yet.</div>
        @endforelse
    </div>

    <div class="p9b-pagination">{{ $audits->links() }}</div>
</section>
@endsection