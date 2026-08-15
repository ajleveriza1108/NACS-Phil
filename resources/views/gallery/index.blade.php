@php($galleryContent=\App\Models\SiteContent::valuesFor('gallery',\App\Support\GalleryContent::defaults()))
@php($localPresentationPreview=app()->environment('local') && is_file(storage_path('app/.nacs-presentation-preview')))
@extends('layouts.gallery-phase7')
@section('title','Gallery')
@section('content')
<section class="g-hero"><div class="g-shell g-hero-inner">
<div data-g-reveal><span class="g-pill">{{ $galleryContent['hero_badge'] }}</span><h1>{{ $galleryContent['hero_heading'] }} <em>{{ $galleryContent['hero_highlight'] }}</em></h1><p>{{ $galleryContent['hero_lead'] }}</p><div class="g-actions"><a class="g-btn primary" href="#photos">Explore Gallery &darr;</a><a class="g-btn secondary" href="{{ route('privacy') }}">Photo Privacy &rarr;</a></div></div>
<div class="g-visual" data-g-reveal><img src="{{ asset('assets/phase7-gallery/gallery-visual.svg') }}" alt="Abstract school photo gallery illustration."></div>
</div></section>
<section id="photos" class="g-section"><div class="g-shell">
<div class="g-section-head" data-g-reveal><div><span>{{ $localPresentationPreview ? 'Local Presentation Preview' : 'Approved Photographs' }}</span><h2>{{ $galleryContent['listing_heading'] }}</h2></div><p>{{ $galleryContent['listing_text'] }}</p></div>
@if($galleryCategories->isNotEmpty())
<nav class="g-filters" aria-label="Gallery categories" data-g-reveal>
<a href="{{ route('gallery.index') }}" @class(['active'=>$activeCategory===''])>All</a>
@foreach($galleryCategories as $category)
<a href="{{ route('gallery.index',['category'=>$category]) }}" @class(['active'=>$activeCategory===$category])>{{ $category }}</a>
@endforeach
</nav>
@endif
<div class="g-grid">
@forelse($galleryItems as $item)
<figure class="g-card" data-g-reveal>
<button type="button" class="g-image" data-g-open data-image="{{ Storage::url($item->image_path) }}" data-alt="{{ $item->alt_text }}" data-title="{{ $item->title }}" data-category="{{ $item->category }}" data-caption="{{ $item->caption ?? '' }}" data-credit="{{ $item->photographer_credit ? 'Photo credit: '.$item->photographer_credit : '' }}" aria-label="Open {{ $item->title }} in image viewer">
<img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->alt_text }}" loading="lazy"><i>+</i></button>
<figcaption><div class="g-meta"><span>{{ $item->category }}</span>@if($item->taken_at)<small>{{ $item->taken_at->format('M Y') }}</small>@endif</div><h3>{{ $item->title }}</h3>@if($item->caption)<p>{{ $item->caption }}</p>@endif @if($item->photographer_credit)<small>Photo credit: {{ $item->photographer_credit }}</small>@endif</figcaption>
</figure>
@empty
<div class="g-empty" data-g-reveal><b>P</b><h3>{{ $galleryContent['empty_heading'] }}</h3><p>{{ $galleryContent['empty_text'] }}</p></div>
@endforelse
</div>
<div class="g-pages">{{ $galleryItems->links() }}</div>
</div></section>
<section class="g-section g-privacy-section"><div class="g-shell"><div class="g-privacy" data-g-reveal><div><span>Responsible Publishing</span><h2>{{ $galleryContent['privacy_heading'] }}</h2><p>{{ $galleryContent['privacy_text'] }}</p></div><a class="g-btn gold" href="{{ route('privacy') }}">{{ $galleryContent['privacy_button'] }} &rarr;</a></div></div></section>
@endsection