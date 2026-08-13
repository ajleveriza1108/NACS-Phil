@extends('admin.layouts.app', ['title' => 'Faculty & Staff'])

@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">School directory</span><h1>Faculty &amp; Staff</h1><p>Manage public school leadership, faculty, and support-staff profiles.</p></div><a class="cm-button cm-button--primary" href="{{ route('admin.faculty.create') }}">Add Profile</a></section>
<section class="cm-panel">
<table class="p12-table">
<thead><tr><th>Name</th><th>Position</th><th>Department</th><th>Public</th><th>Actions</th></tr></thead>
<tbody>
@forelse($profiles as $profile)
<tr>
<td><strong>{{ $profile->name }}</strong></td>
<td>{{ $profile->position }}</td>
<td>{{ $profile->department ?: '-' }}</td>
<td><span class="p12-badge {{ $profile->is_published ? 'p12-badge--good' : '' }}">{{ $profile->is_published ? 'Published' : 'Draft' }}</span></td>
<td class="p12-actions"><a href="{{ route('admin.faculty.edit',$profile) }}">Edit</a><form method="POST" action="{{ route('admin.faculty.destroy',$profile) }}">@csrf @method('DELETE')<button>Archive</button></form></td>
</tr>
@empty<tr><td colspan="5">No faculty profiles yet.</td></tr>@endforelse
</tbody>
</table>
{{ $profiles->links() }}
</section>
@endsection
