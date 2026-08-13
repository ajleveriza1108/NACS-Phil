@extends('admin.layouts.app', ['title' => 'Upload Media'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.media.index') }}">&larr; Media Library</a><h1>Upload Image</h1><p>Store reusable school images with accessibility and authorization metadata.</p></div></section>
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="cm-compose">
@csrf
<label class="cm-field"><span>Title</span><input name="title" required maxlength="180"></label>
<label class="cm-field"><span>Image</span><input type="file" name="file" accept="image/jpeg,image/png,image/webp" required></label>
<label class="cm-field"><span>Alt Text</span><input name="alt_text" required maxlength="250"></label>
<label class="cm-field"><span>Caption</span><textarea name="caption" rows="4"></textarea></label>
<div class="cm-two"><label class="cm-field"><span>Category</span><input name="category" maxlength="100"></label><label class="cm-field"><span>Credit</span><input name="credit" maxlength="180"></label></div>
<div class="cm-consent-box"><label class="cm-check"><input type="checkbox" name="rights_confirmed" value="1" required><span><strong>School has the right to use this image</strong></span></label><label class="cm-check"><input type="checkbox" name="consent_confirmed" value="1"><span><strong>Appropriate consent is confirmed</strong><small>Important for identifiable children.</small></span></label></div>
<button class="cm-button cm-button--primary">Upload</button>
</form>
@endsection
