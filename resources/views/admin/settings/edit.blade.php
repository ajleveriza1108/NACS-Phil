@extends('admin.layouts.app', ['title' => 'School Settings'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Official school information</span><h1>School Settings</h1><p>Manage safe public school identity and contact information. Database credentials, APP_KEY, mail passwords, and hosting secrets are never editable here.</p></div></section>
<form method="POST" action="{{ route('admin.settings.update') }}" class="cm-compose">@csrf @method('PATCH')
@foreach($fields as $key=>$meta)
<label class="cm-field"><span>{{ $meta['label'] }}</span>
@if($key === 'emergency_banner')<textarea name="{{ $key }}" rows="3" maxlength="1000">{{ old($key,$values[$key] ?? '') }}</textarea>
@else<input name="{{ $key }}" value="{{ old($key,$values[$key] ?? '') }}" maxlength="1000">@endif
</label>
@endforeach
<button class="cm-button cm-button--primary">Save School Settings</button>
</form>
@endsection
