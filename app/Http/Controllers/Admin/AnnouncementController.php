<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim($request->string('workflow_status')->toString());

        $query = Announcement::query()->latest('updated_at');

        if ($status !== '' && array_key_exists($status, Announcement::WORKFLOW_STATUSES)) {
            $query->where('workflow_status', $status);
        }

        return view('admin.announcements.index', [
            'announcements' => $query->paginate(15)->withQueryString(),
            'workflowStatuses' => Announcement::WORKFLOW_STATUSES,
            'workflowStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->applyWorkflow($request, $data);
        Announcement::create($data);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement saved.');
    }

    public function edit(Announcement $announcement): View
    {
        if ($requestUser = request()->user()) {
            if ($requestUser->isTeacher() && $announcement->workflow_status === 'published') {
                abort(403, 'Published official content must be changed by the Principal or Super Admin.');
            }
        }

        return view('admin.announcements.form', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        if ($request->user()?->isTeacher() && $announcement->workflow_status === 'published') {
            abort(403);
        }

        $data = $this->validated($request);
        $data = $this->applyWorkflow($request, $data, $announcement);
        $announcement->update($data);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement moved to Trash.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'excerpt' => ['nullable','string','max:400'],
            'body' => ['required','string','max:30000'],
            'type' => ['required', Rule::in(['info','enrollment','event','urgent'])],
            'audience' => ['nullable', Rule::in(['public','parents','applicants','staff'])],
            'starts_at' => ['nullable','date'],
            'ends_at' => ['nullable','date','after_or_equal:starts_at'],
            'publish_at' => ['nullable','date'],
            'sort_order' => ['required','integer','min:0','max:9999'],
        ]);

        unset($data['publish_at']);
        $data['audience'] = $data['audience'] ?? 'public';
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_pinned'] = $request->boolean('is_pinned');
        $data['scheduled_publish_at'] = $request->filled('publish_at') ? $request->date('publish_at') : null;

        return $data;
    }

    private function applyWorkflow(Request $request, array $data, ?Announcement $announcement = null): array
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
            ? ($data['scheduled_publish_at'] ?? $announcement?->published_at ?? now())
            : null;
        $data['reviewed_at'] = $publish ? now() : null;
        $data['reviewed_by_user_id'] = $publish ? $request->user()?->id : null;

        return $data;
    }
}
