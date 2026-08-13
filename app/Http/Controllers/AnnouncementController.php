<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('announcements.index', [
            'announcements' => Announcement::published()
                ->orderByDesc('is_pinned')
                ->latest('published_at')
                ->paginate(9),
        ]);
    }

    public function show(Announcement $announcement): View
    {
        abort_unless(
            Announcement::published()->whereKey($announcement->getKey())->exists(),
            404
        );

        return view('announcements.show', compact('announcement'));
    }
}
