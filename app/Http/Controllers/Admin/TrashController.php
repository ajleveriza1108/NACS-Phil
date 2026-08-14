<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\FacebookMediaItem;
use App\Models\SchoolEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(): View
    {
        return view('admin.trash.index', [
            'announcements' => Announcement::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'events' => SchoolEvent::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'photos' => GalleryItem::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'mediaItems' => FacebookMediaItem::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
        ]);
    }

    public function restore(string $type, int $id): RedirectResponse
    {
        $item = $this->find($type, $id);
        $item->restore();

        return back()->with('success', $this->label($type).' restored.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        abort_unless(request()->user()?->isSuperAdmin(), 403);

        $item = $this->find($type, $id);

        if ($item instanceof GalleryItem) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->forceDelete();

        return back()->with('success', $this->label($type).' permanently deleted.');
    }

    private function find(string $type, int $id): Model
    {
        $class = match ($type) {
            'announcement' => Announcement::class,
            'event' => SchoolEvent::class,
            'photo' => GalleryItem::class,
            'media' => FacebookMediaItem::class,
            default => abort(404),
        };

        return $class::onlyTrashed()->findOrFail($id);
    }

    private function label(string $type): string
    {
        return match ($type) {
            'announcement' => 'Announcement',
            'event' => 'Event',
            'photo' => 'Photo',
            'media' => 'Facebook media link',
            default => 'Item',
        };
    }
}