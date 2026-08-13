@extends('admin.layouts.app', ['title' => 'Content Reviews'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Teacher to Principal</span><h1>Content Reviews</h1><p>Approve teacher-submitted announcements, events, and gallery photos before they become public.</p></div></section>
<section class="cm-panel">
<table class="p12-table"><thead><tr><th>Type</th><th>Title</th><th>Submitted</th><th>Decision</th></tr></thead><tbody>
@forelse($pending as $item)
<tr><td><span class="p12-badge p12-badge--warn">{{ ucfirst($item['type']) }}</span></td><td><strong>{{ $item['title'] }}</strong></td><td>{{ $item['submitted_for_review_at']?->format('M j, Y g:i A') ?: 'Pending' }}</td><td>
<form method="POST" action="{{ route('admin.reviews.decide',[$item['type'],$item['id']]) }}" class="p12-actions">@csrf @method('PATCH')
<input name="review_notes" placeholder="Optional review note" maxlength="5000">
<button name="decision" value="approve" class="cm-button cm-button--primary">Approve</button>
<button name="decision" value="changes_requested" class="cm-button cm-button--secondary">Request Changes</button>
</form></td></tr>
@empty<tr><td colspan="4">No content is waiting for review.</td></tr>@endforelse
</tbody></table>
</section>
@endsection
