@extends('admin.layouts.app', ['title' => 'Admissions Applications'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Private school records</span><h1>Admissions Applications</h1><p>Search, filter, review, and track preliminary applications. Sensitive records remain restricted to Principal and Super Admin.</p></div></section>
<form method="GET" class="cm-compose" style="margin-bottom:18px">
<div class="cm-two"><label class="cm-field"><span>Search</span><input name="search" value="{{ $search }}" placeholder="Reference, guardian, learner, email, phone"></label><label class="cm-field"><span>Status</span><select name="status"><option value="">All</option>@foreach($statuses as $v=>$l)<option value="{{ $v }}" @selected($status===$v)>{{ $l }}</option>@endforeach</select></label></div>
<div class="cm-two"><label class="cm-field"><span>School Year</span><select name="school_year"><option value="">All</option>@foreach($schoolYears as $year)<option value="{{ $year }}" @selected($schoolYear===$year)>{{ $year }}</option>@endforeach</select></label><label class="cm-field"><span>Level</span><select name="level"><option value="">All</option>@foreach($levels as $item)<option value="{{ $item }}" @selected($level===$item)>{{ $item }}</option>@endforeach</select></label></div>
<button class="cm-button cm-button--secondary">Filter</button>
</form>
<section class="cm-panel"><table class="p12-table"><thead><tr><th>Reference</th><th>Learner</th><th>Level</th><th>Status</th><th>Documents</th></tr></thead><tbody>
@forelse($applications as $application)<tr><td><a href="{{ route('admin.admissions.show',$application) }}"><strong>{{ $application->reference_code }}</strong></a><br><small>{{ $application->submitted_at?->format('M j, Y') }}</small></td><td>{{ $application->student_name }}<br><small>{{ $application->guardian_name }}</small></td><td>{{ $application->applying_for_level }}<br><small>{{ $application->school_year }}</small></td><td><span class="p12-badge">{{ $statuses[$application->status] ?? $application->status }}</span></td><td>{{ $application->documents_count }}</td></tr>@empty<tr><td colspan="5">No applications match the filter.</td></tr>@endforelse
</tbody></table>{{ $applications->links() }}</section>
@endsection
