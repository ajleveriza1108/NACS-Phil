@extends('admin.layouts.app', ['title' => 'Inquiry CRM'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">School office</span><h1>Inquiry CRM Lite</h1><p>Track new family inquiries, assignments, follow-ups, and resolution without a heavy external CRM.</p></div></section>
<form method="GET" class="cm-compose" style="margin-bottom:18px"><div class="cm-two"><label class="cm-field"><span>Search</span><input name="search" value="{{ $search }}" placeholder="Guardian, learner, email, phone"></label><label class="cm-field"><span>Status</span><select name="status"><option value="">All</option>@foreach($statuses as $v=>$l)<option value="{{ $v }}" @selected($status===$v)>{{ $l }}</option>@endforeach</select></label></div><button class="cm-button cm-button--secondary">Filter</button></form>
<section class="cm-panel"><table class="p12-table"><thead><tr><th>Family</th><th>Interest</th><th>Status</th><th>Assigned</th><th>Follow-up</th></tr></thead><tbody>
@forelse($inquiries as $inquiry)<tr><td><a href="{{ route('admin.inquiries.show',$inquiry) }}"><strong>{{ $inquiry->guardian_name }}</strong></a><br><small>{{ $inquiry->student_name ?: 'No learner name' }}</small></td><td>{{ $inquiry->level_interested ?: '-' }}</td><td><span class="p12-badge">{{ $statuses[$inquiry->status] ?? $inquiry->status }}</span></td><td>{{ $inquiry->assignedTo?->name ?: 'Unassigned' }}</td><td>{{ $inquiry->follow_up_at?->format('M j, Y') ?: '-' }}</td></tr>@empty<tr><td colspan="5">No inquiries.</td></tr>@endforelse
</tbody></table>{{ $inquiries->links() }}</section>
@endsection
