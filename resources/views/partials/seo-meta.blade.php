@php($nacsSeo = \App\Models\SeoSetting::forCurrentRequest())
@if($nacsSeo)
    @if($nacsSeo->meta_description)<meta name="description" content="{{ $nacsSeo->meta_description }}">@endif
    <meta name="robots" content="{{ $nacsSeo->no_index ? 'noindex,nofollow' : 'index,follow' }}">
    @if($nacsSeo->canonical_url)<link rel="canonical" href="{{ $nacsSeo->canonical_url }}">@endif
    <meta property="og:site_name" content="{{ \App\Models\SchoolSetting::valueFor('short_name', config('nacs.short_name')) }}">
    <meta property="og:title" content="{{ $nacsSeo->social_title ?: $nacsSeo->title ?: config('nacs.short_name') }}">
    @if($nacsSeo->social_description || $nacsSeo->meta_description)<meta property="og:description" content="{{ $nacsSeo->social_description ?: $nacsSeo->meta_description }}">@endif
    @if($nacsSeo->social_image_path)<meta property="og:image" content="{{ Storage::url($nacsSeo->social_image_path) }}">@endif
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $nacsSeo->social_title ?: $nacsSeo->title ?: config('nacs.short_name') }}">
    @if($nacsSeo->social_description || $nacsSeo->meta_description)<meta name="twitter:description" content="{{ $nacsSeo->social_description ?: $nacsSeo->meta_description }}">@endif
@endif
