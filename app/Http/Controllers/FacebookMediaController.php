<?php

namespace App\Http\Controllers;

use App\Models\FacebookMediaItem;
use Illuminate\View\View;

class FacebookMediaController extends Controller
{
    public function index(): View
    {
        $items = FacebookMediaItem::published()
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('media.index', compact('items'));
    }
}
