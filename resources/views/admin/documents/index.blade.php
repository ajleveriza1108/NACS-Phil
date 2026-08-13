@extends('admin.layouts.app', ['title' => 'Documents'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">School resources</span><h1>Document Center</h1><p>Public and staff-facing school files. Public downloads are served through controlled routes.</p></div><a class="cm-button cm-button--primary" href="{{ route('admin.documents.create') }}">Add Document</a></section>
<section class="cm-panel">
<table class="p12-table"><thead><tr><th>Title</th><th>Category</th><th>Audience</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($documents as $document)
<tr><td><strong>{{ $document->title }}</strong><br><small>{{ $document->original_name }}</small></td><td>{{ $document->category }}</td><td>{{ ucfirst($document->audience) }}</td><td><span class="p12-badge {{ $document->published_at ? 'p12-badge--good' : '' }}">{{ $document->published_at ? 'Published' : 'Draft' }}</span></td><td class="p12-actions"><a href="{{ route('admin.documents.download',$document) }}">Download</a><a href="{{ route('admin.documents.edit',$document) }}">Edit</a><form method="POST" action="{{ route('admin.documents.destroy',$document) }}">@csrf @method('DELETE')<button>Archive</button></form></td></tr>
@empty<tr><td colspan="5">No documents yet.</td></tr>@endforelse
</tbody></table>{{ $documents->links() }}
</section>
@endsection
