<?php

namespace App\Http\Controllers;

use App\Models\SchoolEvent;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        return view('events.index', [
            'upcomingEvents' => SchoolEvent::published()
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->paginate(12),
        ]);
    }
}
