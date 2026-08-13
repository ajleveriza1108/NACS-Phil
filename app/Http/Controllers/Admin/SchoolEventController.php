<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolEventController extends Controller
{
    public function index(): View
    {
        return view('admin.events.index', [
            'events' => SchoolEvent::orderByDesc('starts_at')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.events.form');
    }

    public function store(Request $request): RedirectResponse
    {
        SchoolEvent::create($this->validated($request));

        return redirect()->route('admin.events.index')->with('success', 'Event created.');
    }

    public function edit(SchoolEvent $event): View
    {
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, SchoolEvent $event): RedirectResponse
    {
        $event->update($this->validated($request, $event));

        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(SchoolEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    private function validated(Request $request, ?SchoolEvent $event = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:30000'],
            'venue' => ['nullable', 'string', 'max:180'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'registration_url' => ['nullable', 'url:http,https', 'max:500'],
        ]);

        $data['is_all_day'] = $request->boolean('is_all_day');
        $data['published_at'] = $request->boolean('is_published')
            ? ($event?->published_at ?? now())
            : null;

        return $data;
    }
}
