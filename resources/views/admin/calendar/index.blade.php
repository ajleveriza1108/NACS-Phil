@extends('admin.layouts.app', ['title' => 'Academic Calendar'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">School year</span><h1>Academic Calendar</h1><p>Manage academic dates separately from event stories.</p></div><a class="cm-button cm-button--primary" href="{{ route('admin.calendar.create') }}">Add Date</a></section>
<section class="cm-panel"><table class="p12-table"><thead><tr><th>Date</th><th>Title</th><th>Category</th><th>Public</th><th>Actions</th></tr></thead><tbody>
@forelse($entries as $entry)<tr><td>{{ $entry->starts_at->format('M j, Y') }}</td><td><strong>{{ $entry->title }}</strong></td><td>{{ \App\Models\AcademicCalendarEntry::CATEGORIES[$entry->category] ?? $entry->category }}</td><td>{{ $entry->is_published ? 'Yes' : 'No' }}</td><td class="p12-actions"><a href="{{ route('admin.calendar.edit',$entry) }}">Edit</a><form method="POST" action="{{ route('admin.calendar.destroy',$entry) }}">@csrf @method('DELETE')<button>Archive</button></form></td></tr>@empty<tr><td colspan="5">No calendar entries.</td></tr>@endforelse
</tbody></table>{{ $entries->links() }}</section>
@endsection
