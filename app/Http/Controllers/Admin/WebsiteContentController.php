<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\HomeContent;
use App\Support\VisualEditorSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.website-content.edit', [
            'content' => SiteContent::valuesFor('home', HomeContent::defaults()),
            'schema' => VisualEditorSchema::home(),
            'imageRule' => VisualEditorSchema::homeImage(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = VisualEditorSchema::validationRules();

        $rules['hero_image_focus_x'] = ['required', 'numeric', 'min:0', 'max:100'];
        $rules['hero_image_focus_y'] = ['required', 'numeric', 'min:0', 'max:100'];
        $rules['hero_image_zoom'] = ['required', 'numeric', 'min:1', 'max:2'];
        $rules['hero_image'] = [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
            'dimensions:min_width=1200,min_height=750',
        ];

        $rules['hero_image_authorized'] = $request->hasFile('hero_image')
            ? ['required', 'accepted']
            : ['nullable'];

        $validated = $request->validate($rules, [
            'hero_image.dimensions' => 'The homepage hero image must be at least 1200 x 750 pixels. Portrait images are allowed and can be repositioned inside the locked frame.',
            'hero_image.max' => 'The homepage hero image must be 5 MB or smaller.',
            'hero_image_authorized.required' => 'Confirm that the new homepage photograph is approved for website publication.',
            'hero_image_authorized.accepted' => 'Confirm that the new homepage photograph is approved for website publication.',
        ]);

        unset($validated['hero_image'], $validated['hero_image_authorized']);

        $current = SiteContent::valuesFor('home', HomeContent::defaults());

        if ($request->hasFile('hero_image')) {
            $newPath = $request->file('hero_image')->store('site/home', 'public');
            $oldPath = (string) ($current['hero_image_path'] ?? '');

            $validated['hero_image_path'] = $newPath;
            SiteContent::storeValues('home', $validated);

            if ($oldPath !== '' && $oldPath !== $newPath && str_starts_with($oldPath, 'site/home/')) {
                Storage::disk('public')->delete($oldPath);
            }
        } else {
            SiteContent::storeValues('home', $validated);
        }

        return back()->with('success', 'Homepage content saved. The layout remains locked; review the live page preview before publishing additional changes.');
    }
}
