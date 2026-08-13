@extends('admin.layouts.app', ['title' => 'Official Branding'])

@section('content')
<section class="p16-head">
    <div>
        <span class="cm-eyebrow">Official school identity</span>
        <h1>Branding Manager</h1>
        <p>Replace the development mark only with a logo or crest that the school has officially approved for public use.</p>
    </div>
    <a class="cm-button cm-button--secondary" href="{{ route('admin.launch-readiness') }}">Launch Readiness</a>
</section>

<div class="p16-grid">
    <section class="p16-panel">
        <div class="p16-panel-head">
            <div><span class="cm-eyebrow">Current identity</span><h2>Logo preview</h2></div>
            <span class="p16-status {{ $hasOfficialLogo ? 'is-approved' : 'is-development' }}">
                {{ $hasOfficialLogo ? 'Official / Approved Upload' : 'Official Logo / Built-in' }}
            </span>
        </div>

        <div class="p16-preview">
            <div class="p16-logo-box">
                <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}">
            </div>
            <div>
                <strong>{{ $logoAlt }}</strong>
                @if($hasOfficialLogo && $approvedAt)
                    <small>Approval recorded {{ \Illuminate\Support\Carbon::parse($approvedAt)->format('M j, Y g:i A') }}</small>
                @else
                    <small>The enhanced official school logo is displayed. Upload and approve it here to record Launch Readiness approval.</small>
                @endif
            </div>
        </div>

        <div class="p16-rule">
            <strong>Accepted files</strong>
            <p>PNG, JPG/JPEG, or WebP; maximum 2 MB; 128-4000 px in both dimensions. SVG uploads are intentionally not accepted because uploaded SVG can contain executable browser content.</p>
        </div>
    </section>

    <section class="p16-panel">
        <div class="p16-panel-head">
            <div><span class="cm-eyebrow">Principal / Super Admin</span><h2>Upload approved logo</h2></div>
        </div>

        <form method="POST" action="{{ route('admin.branding.store') }}" enctype="multipart/form-data" class="p16-form" data-cm-form>
            @csrf

            <label class="cm-field">
                <span>Official logo file</span>
                <input type="file" name="official_logo" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp" required>
                <small>For a crest/seal, a clear square PNG with a transparent background usually works best.</small>
            </label>

            <label class="cm-field">
                <span>Logo alternative text</span>
                <input name="official_logo_alt" maxlength="160" required value="{{ old('official_logo_alt', $logoAlt) }}">
                <small>Example: NACS-Phil official school logo.</small>
            </label>

            <label class="p16-approval">
                <input type="checkbox" name="official_branding_approved" value="1" required>
                <span>
                    <strong>I confirm this logo/crest is officially approved for public school use.</strong>
                    <small>Do not check this box for a concept, placeholder, AI-generated mark, or an image copied from an unverified source.</small>
                </span>
            </label>

            <button class="cm-button cm-button--primary">Upload &amp; Activate Official Logo</button>
        </form>

        @if($hasOfficialLogo)
            <form method="POST" action="{{ route('admin.branding.destroy') }}" class="p16-remove" data-cm-confirm="Remove the official logo and return the website to the development mark?">
                @csrf
                @method('DELETE')
                <button type="submit">Remove Official Logo</button>
            </form>
        @endif
    </section>
</div>

<section class="p16-storage">
    <strong>Deployment note</strong>
    <p>The uploaded logo is runtime media, not source code. It stays out of GitHub and must be included with the approved public-storage backup when the website is deployed.</p>
</section>
@endsection
