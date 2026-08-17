@extends('admin.layouts.app')

@section('content')
<div class="cm-page-head">
    <div>
        <p class="cm-eyebrow">Website</p>
        <h1>Header &amp; Navigation</h1>
        <p>Edit the public header wording and the watermark used on school-generated academic PDFs. This does not change code, credentials, or hosting settings.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Website</a>
</div>

<form method="POST" action="{{ route('admin.header.update') }}" class="cm-card">
    @csrf
    @method('PATCH')

    <div class="cm-form-grid">
        @foreach($fields as $key => $meta)
            <label class="cm-field">
                <span>{{ $meta['label'] }}</span>
                <input
                    type="text"
                    name="{{ $key }}"
                    value="{{ old($key, $values[$key] ?? '') }}"
                    maxlength="{{ $meta['max'] }}"
                    autocomplete="off"
                >
                @if($key === 'document_watermark')
                    <small>Keep this short. It appears behind Report Card and TOR PDF content.</small>
                @elseif(str_starts_with($key, 'header_nav_') || str_contains($key, '_label'))
                    <small>Navigation labels should stay short so the header remains responsive on tablets and phones.</small>
                @endif
            </label>
        @endforeach
    </div>

    <div class="cm-form-actions">
        <button type="submit" class="cm-button">Save Header</button>
    </div>
</form>
@endsection
