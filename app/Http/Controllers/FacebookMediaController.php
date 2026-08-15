<?php

namespace App\Http\Controllers;

use App\Models\FacebookMediaItem;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FacebookMediaController extends Controller
{
    public function index(Request $request): View
    {
        $filter = strtolower(trim($request->string('type', 'all')->toString()));

        if (! in_array($filter, ['all', 'photos', 'videos', 'live'], true)) {
            $filter = 'all';
        }

        $photos = new Collection();

        if (in_array($filter, ['all', 'photos'], true)) {
            $photos = GalleryItem::published()
                ->orderBy('sort_order')
                ->latest('taken_at')
                ->limit(8)
                ->get();
        }

        $items = new Collection();

        if ($filter !== 'photos') {
            $query = FacebookMediaItem::published()
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at');

            if ($filter === 'videos') {
                $query->where('media_type', 'video');
            } elseif ($filter === 'live') {
                $query->where('media_type', 'live');
            }

            $items = $query->paginate(12)->withQueryString();
        }

        return view('media.index', [
            'items' => $items,
            'photos' => $photos,
            'activeType' => $filter,
            'photoCount' => GalleryItem::published()->count(),
            'videoCount' => FacebookMediaItem::published()->where('media_type', 'video')->count(),
            'liveCount' => FacebookMediaItem::published()->where('media_type', 'live')->count(),
        ]);
    }
}
