<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('gallery.index', [
            'galleryItems' => GalleryItem::published()
                ->orderBy('sort_order')
                ->latest('taken_at')
                ->paginate(18),
        ]);
    }
}
