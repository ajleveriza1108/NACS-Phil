@extends('admin.layouts.app', ['title' => 'Inquiry'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.inquiries.index') }}">&larr; Inquiry CRM</a><span class="cm-eyebrow">Family inquiry</span><h1>{{ $inquiry->guardian_name }}</h1><p>{{ $inquiry->student_name ?: 'Learner name not provided' }} &middot; {{ $inquiry->level_interested ?: 'Program not specified' }}</p></div></section>
<div class="p12-grid">
<section class="p12-card"><h2>Contact</h2><p>Email: {{ $inquiry->email ?: 'Not provided' }}</p><p>Phone: {{ $inquiry->phone ?: 'Not provided' }}</p><p>Message:</p><p>{{ $inquiry->message }}</p></section>
<section class="p12-card"><h2>Office Workflow</h2>
<form method="POST" action="{{ route('admin.inquiries.update',$inquiry) }}" class="cm-compose">@csrf @method('PATCH')
<label class="cm-field"><span>Status</span><select name="status">@foreach($statuses as $v=>$l)<option value="{{ $v }}" @selected(old('status',$inquiry->status)===$v)>{{ $l }}</option>@endforeach</select></label>
<label class="cm-field"><span>Assigned Staff</span><select name="assigned_to_user_id"><option value="">Unassigned</option>@foreach($staff as $person)<option value="{{ $person->id }}" @selected(old('assigned_to_user_id',$inquiry->assigned_to_user_id)==$person->id)>{{ $person->name }} - {{ $person->staffRoleLabel() }}</option>@endforeach</select></label>
<div class="cm-two"><label class="cm-field"><span>Follow-up Date</span><input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at',$inquiry->follow_up_at?->format('Y-m-d\TH:i')) }}"></label><label class="cm-field"><span>Last Contacted</span><input type="datetime-local" name="last_contacted_at" value="{{ old('last_contacted_at',$inquiry->last_contacted_at?->format('Y-m-d\TH:i')) }}"></label></div>
<div class="cm-two"><label class="cm-field"><span>Source</span><input name="source" value="{{ old('source',$inquiry->source) }}" maxlength="80" placeholder="Website, Facebook, Walk-in..."></label><label class="cm-field"><span>Interest Level</span><select name="interest_level"><option value="">Not set</option>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High'] as $v=>$l)<option value="{{ $v }}" @selected(old('interest_level',$inquiry->interest_level)===$v)>{{ $l }}</option>@endforeach</select></label></div>
<label class="cm-field"><span>Private Staff Notes</span><textarea name="admin_notes" rows="7">{{ old('admin_notes',$inquiry->admin_notes) }}</textarea></label>
<button class="cm-button cm-button--primary">Save Inquiry</button></form>
</section></div>
@endsection
