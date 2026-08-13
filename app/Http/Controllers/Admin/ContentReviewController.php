<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\SchoolEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentReviewController extends Controller
{
    public function index(): View
    {
        $pending = collect()
            ->merge($this->rows(Announcement::query()->where('workflow_status', 'pending_review')->get(), 'announcement'))
            ->merge($this->rows(SchoolEvent::query()->where('workflow_status', 'pending_review')->get(), 'event'))
            ->merge($this->rows(GalleryItem::query()->where('workflow_status', 'pending_review')->get(), 'gallery'))
            ->sortByDesc('submitted_for_review_at')
            ->values();

        return view('admin.reviews.index', compact('pending'));
    }

    public function decide(Request $request, string $type, int $id): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approve','changes_requested'])],
            'review_notes' => ['nullable','string','max:5000'],
        ]);

        $model = $this->findModel($type, $id);
        abort_unless($model->workflow_status === 'pending_review', 422);

        if ($data['decision'] === 'approve') {
            if ($model instanceof GalleryItem) {
                if (! $model->consent_confirmed_at) {
                    return back()->withErrors(['review' => 'Gallery publication is blocked until authorization and consent are confirmed.']);
                }

                $model->is_published = true;
            } else {
                $model->published_at = $model->scheduled_publish_at ?? now();
            }

            $model->workflow_status = 'published';
        } else {
            if ($model instanceof GalleryItem) {
                $model->is_published = false;
            } else {
                $model->published_at = null;
            }

            $model->workflow_status = 'changes_requested';
        }

        $model->reviewed_at = now();
        $model->reviewed_by_user_id = $request->user()?->id;
        $model->review_notes = $data['review_notes'] ?? null;
        $model->save();

        return back()->with('success', $data['decision'] === 'approve'
            ? 'Content approved and publication rules applied.'
            : 'Changes requested. The content remains unpublished.');
    }

    private function rows(Collection $models, string $type): Collection
    {
        return $models->map(fn ($model) => [
            'type' => $type,
            'id' => $model->id,
            'title' => $model->title,
            'submitted_for_review_at' => $model->submitted_for_review_at,
        ]);
    }

    private function findModel(string $type, int $id): Announcement|SchoolEvent|GalleryItem
    {
        return match ($type) {
            'announcement' => Announcement::query()->findOrFail($id),
            'event' => SchoolEvent::query()->findOrFail($id),
            'gallery' => GalleryItem::query()->findOrFail($id),
            default => abort(404),
        };
    }
}
