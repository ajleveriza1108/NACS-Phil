<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyProfile;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\InputBag;

class MediaAssetController extends Controller
{
    public function index(Request $request): View
    {
        $category = trim($request->string('category')->toString());

        $query = MediaAsset::query()->latest();

        if ($category !== '') {
            $query->where('category', $category);
        }

        return view('admin.media.index', [
            'assets' => $query->paginate(24)->withQueryString(),
            'category' => $category,
        ]);
    }

    public function create(): View
    {
        return view('admin.media.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->requestPayload($request);
        $file = $this->uploadedFile($request, 'file');

        if ($file) {
            $payload['file'] = $file;
        }

        $data = Validator::make($payload, [
            'title' => ['required','string','max:180'],
            'file' => ['required','image','mimes:jpg,jpeg,png,webp','max:5120'],
            'alt_text' => ['required','string','max:250'],
            'caption' => ['nullable','string','max:3000'],
            'category' => ['nullable','string','max:100'],
            'credit' => ['nullable','string','max:180'],
            'rights_confirmed' => ['required','accepted'],
            'consent_confirmed' => ['sometimes','accepted'],
        ])->validate();

        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store('media-library', 'public');

        MediaAsset::create([
            'title' => $data['title'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $data['alt_text'],
            'caption' => $data['caption'] ?? null,
            'category' => $data['category'] ?? null,
            'rights_confirmed_at' => now(),
            'consent_confirmed_at' => $this->truthy($payload['consent_confirmed'] ?? null) ? now() : null,
            'credit' => $data['credit'] ?? null,
            'uploaded_by_user_id' => $request->user()?->id,
        ]);

        return redirect()->route('admin.media.index')->with('success', 'Media asset uploaded.');
    }

    public function destroy(Request $request, MediaAsset $medium): RedirectResponse
    {
        if ($request->user()?->isTeacher()) {
            abort(403);
        }

        $path = $medium->file_path;
        $inUse = FacultyProfile::withTrashed()->where('photo_path', $path)->exists()
            || GalleryItem::withTrashed()->where('image_path', $path)->exists()
            || SiteContent::query()->where('value', $path)->exists();

        if ($inUse) {
            return back()->withErrors([
                'media' => 'This asset is currently referenced by website content and cannot be deleted.',
            ]);
        }

        $medium->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media asset moved to Safe Trash. The file was kept for recovery.');
    }

    private function requestPayload(Request $request): array
    {
        $bag = $request->request;

        if ($bag instanceof InputBag) {
            return $bag->all();
        }

        return is_array($bag) ? $bag : [];
    }

    private function uploadedFile(Request $request, string $key): ?UploadedFile
    {
        $files = $request->files;

        if ($files instanceof FileBag) {
            $file = $files->get($key);
        } elseif (is_array($files)) {
            $file = $files[$key] ?? null;
        } else {
            $file = null;
        }

        return $file instanceof UploadedFile ? $file : null;
    }

    private function truthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
