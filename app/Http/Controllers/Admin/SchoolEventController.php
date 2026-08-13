<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolEventController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim($request->string('workflow_status')->toString());
        $query = SchoolEvent::query()->orderByDesc('starts_at');

        if (in_array($status, ['draft','pending_review','changes_requested','published','archived'], true)) {
            $query->where('workflow_status', $status);
        }

        return view('admin.events.index', [
            'events' => $query->paginate(15)->withQueryString(),
            'workflowStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.events.form');
    }

    public function store(Request $request): RedirectResponse
    {
        SchoolEvent::create($this->applyWorkflow($request, $this->validated($request)));

        return redirect()->route('admin.events.index')->with('success', 'Event saved.');
    }

    public function edit(SchoolEvent $event): View
    {
        if (request()->user()?->isTeacher() && $event->workflow_status === 'published') {
            abort(403);
        }

        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, SchoolEvent $event): RedirectResponse
    {
        if ($request->user()?->isTeacher() && $event->workflow_status === 'published') {
            abort(403);
        }

        $event->update($this->applyWorkflow($request, $this->validated($request), $event));

        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(SchoolEvent $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event moved to Trash.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'description' => ['required','string','max:30000'],
            'venue' => ['nullable','string','max:180'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after_or_equal:starts_at'],
            'registration_url' => ['nullable','url:http,https','max:500'],
            'publish_at' => ['nullable','date'],
        ]);

        $data['is_all_day'] = $request->boolean('is_all_day');
        $data['scheduled_publish_at'] = $request->filled('publish_at') ? $request->date('publish_at') : null;
        unset($data['publish_at']);

        return $data;
    }

    private function applyWorkflow(Request $request, array $data, ?SchoolEvent $event = null): array
    {
        if ($request->user()?->isTeacher()) {
            $submit = $request->input('action') === 'submit_review';
            $data['workflow_status'] = $submit ? 'pending_review' : 'draft';
            $data['submitted_for_review_at'] = $submit ? now() : null;
            $data['published_at'] = null;
            $data['reviewed_at'] = null;
            $data['reviewed_by_user_id'] = null;
            $data['review_notes'] = null;

            return $data;
        }

        $publish = $request->boolean('is_published');
        $data['workflow_status'] = $publish ? 'published' : 'draft';
        $data['published_at'] = $publish
            ? ($data['scheduled_publish_at'] ?? $event?->published_at ?? now())
            : null;
        $data['reviewed_at'] = $publish ? now() : null;
        $data['reviewed_by_user_id'] = $publish ? $request->user()?->id : null;

        return $data;
    }
}
