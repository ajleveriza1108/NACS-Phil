<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendarEntry;
use App\Models\Announcement;
use App\Models\FacultyProfile;
use App\Models\FacebookMediaItem;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\SchoolDocument;
use App\Models\SchoolEvent;
use App\Models\SiteContent;
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
            'facultyProfiles' => FacultyProfile::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'documents' => SchoolDocument::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'calendarEntries' => AcademicCalendarEntry::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
            'mediaAssets' => MediaAsset::onlyTrashed()->latest('deleted_at')->limit(100)->get(),
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

        if ($item instanceof MediaAsset && $this->mediaAssetInUse($item)) {
            return back()->withErrors([
                'media' => 'This media asset is still referenced by website content and cannot be permanently deleted.',
            ]);
        }

        if ($item instanceof GalleryItem) {
            Storage::disk('public')->delete($item->image_path);
        }

        if ($item instanceof MediaAsset) {
            Storage::disk('public')->delete($item->file_path);
        }

        if ($item instanceof SchoolDocument) {
            Storage::build([
                'driver' => 'local',
                'root' => storage_path('app/private/documents'),
                'throw' => true,
            ])->delete($item->file_path);
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
            'faculty' => FacultyProfile::class,
            'document' => SchoolDocument::class,
            'calendar' => AcademicCalendarEntry::class,
            'asset' => MediaAsset::class,
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
            'faculty' => 'Faculty or staff profile',
            'document' => 'Document',
            'calendar' => 'Calendar entry',
            'asset' => 'Media asset',
            default => 'Item',
        };
    }

    private function mediaAssetInUse(MediaAsset $asset): bool
    {
        $path = $asset->file_path;

        return FacultyProfile::withTrashed()->where('photo_path', $path)->exists()
            || GalleryItem::withTrashed()->where('image_path', $path)->exists()
            || SiteContent::query()->where('value', $path)->exists();
    }
}
