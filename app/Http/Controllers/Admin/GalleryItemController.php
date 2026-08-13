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
    public function index(): View
    {
        return view('admin.gallery.index', [
            'items' => GalleryItem::orderBy('sort_order')->latest()->paginate(15),
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
        GalleryItem::create($data);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery item created.');
    }

    public function edit(GalleryItem $galleryItem): View
    {
        return view('admin.gallery.form', compact('galleryItem'));
    }

    public function update(Request $request, GalleryItem $galleryItem): RedirectResponse
    {
        $data = $this->validated($request, false);

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
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'string', 'max:80'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt_text' => ['required', 'string', 'max:250'],
            'caption' => ['nullable', 'string', 'max:3000'],
            'taken_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'photographer_credit' => ['nullable', 'string', 'max:180'],
            'consent_confirmed' => [
                'nullable',
                Rule::requiredIf(fn (): bool => $request->boolean('is_published')),
                'accepted',
            ],
        ], [
            'consent_confirmed.required' => 'Confirm school authorization and appropriate consent before publishing this image.',
            'consent_confirmed.accepted' => 'Confirm school authorization and appropriate consent before publishing this image.',
        ]);

        unset($data['image'], $data['consent_confirmed']);
        $data['is_published'] = $request->boolean('is_published');
        $data['consent_confirmed_at'] = $request->boolean('consent_confirmed') ? now() : null;

        return $data;
    }
}