<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GalleryItemController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim($request->string('workflow_status')->toString());
        $query = GalleryItem::query()->orderBy('sort_order')->latest();

        if (in_array($status, ['draft','pending_review','changes_requested','published','archived'], true)) {
            $query->where('workflow_status', $status);
        }

        return view('admin.gallery.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'workflowStatus' => $status,
        ]);
    }

    public function create(): View
    {
        return view('admin.gallery.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $data['image_path'] = $request->file('image')->store('gallery', 'public');
        $data = $this->applyWorkflow($request, $data);
        GalleryItem::create($data);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery item saved.');
    }

    public function edit(GalleryItem $galleryItem): View
    {
        if (request()->user()?->isTeacher() && $galleryItem->workflow_status === 'published') {
            abort(403);
        }

        return view('admin.gallery.form', compact('galleryItem'));
    }

    public function update(Request $request, GalleryItem $galleryItem): RedirectResponse
    {
        if ($request->user()?->isTeacher() && $galleryItem->workflow_status === 'published') {
            abort(403);
        }

        $data = $this->validated($request, false);
        $data = $this->applyWorkflow($request, $data, $galleryItem);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('gallery', 'public');
            $oldPath = $galleryItem->image_path;
            $data['image_path'] = $newPath;
            $galleryItem->update($data);
            Storage::disk('public')->delete($oldPath);
        } else {
            $galleryItem->update($data);
        }

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery item updated.');
    }

    public function destroy(GalleryItem $galleryItem): RedirectResponse
    {
        $galleryItem->delete();

        return redirect()->route('admin.gallery.index')->with('success', 'Photo moved to Trash. The image file was kept for recovery.');
    }

    private function validated(Request $request, bool $imageRequired): array
    {
        $publishingByManager = ! $request->user()?->isTeacher() && $request->boolean('is_published');

        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'category' => ['required','string','max:80'],
            'image' => [$imageRequired ? 'required' : 'nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'alt_text' => ['required','string','max:250'],
            'caption' => ['nullable','string','max:3000'],
            'taken_at' => ['nullable','date'],
            'sort_order' => ['required','integer','min:0','max:9999'],
            'photographer_credit' => ['nullable','string','max:180'],
            'consent_confirmed' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $publishingByManager),
                'accepted',
            ],
        ], [
            'consent_confirmed.required' => 'Confirm school authorization and appropriate consent before publishing this image.',
            'consent_confirmed.accepted' => 'Confirm school authorization and appropriate consent before publishing this image.',
        ]);

        unset($data['image'], $data['consent_confirmed']);

        if ($request->boolean('consent_confirmed')) {
            $data['consent_confirmed_at'] = now();
        }

        return $data;
    }

    private function applyWorkflow(Request $request, array $data, ?GalleryItem $item = null): array
    {
        if ($request->user()?->isTeacher()) {
            $submit = $request->input('action') === 'submit_review';
            $data['workflow_status'] = $submit ? 'pending_review' : 'draft';
            $data['submitted_for_review_at'] = $submit ? now() : null;
            $data['is_published'] = false;
            $data['reviewed_at'] = null;
            $data['reviewed_by_user_id'] = null;
            $data['review_notes'] = null;

            return $data;
        }

        $publish = $request->boolean('is_published');
        $data['workflow_status'] = $publish ? 'published' : 'draft';
        $data['is_published'] = $publish;
        $data['reviewed_at'] = $publish ? now() : null;
        $data['reviewed_by_user_id'] = $publish ? $request->user()?->id : null;

        return $data;
    }
}
