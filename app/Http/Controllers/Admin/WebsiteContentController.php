<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\HomeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.website-content.edit', [
            'content' => SiteContent::valuesFor('home', HomeContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'hero_badge' => ['required', 'string', 'max:80'],
            'hero_heading' => ['required', 'string', 'max:120'],
            'hero_highlight' => ['required', 'string', 'max:120'],
            'hero_lead' => ['required', 'string', 'max:600'],
            'hero_primary_button' => ['required', 'string', 'max:40'],
            'hero_secondary_button' => ['required', 'string', 'max:40'],
            'hero_image_alt' => ['required', 'string', 'max:250'],

            'why_heading' => ['required', 'string', 'max:160'],
            'why_intro' => ['required', 'string', 'max:600'],
            'why_1_title' => ['required', 'string', 'max:80'],
            'why_1_text' => ['required', 'string', 'max:500'],
            'why_2_title' => ['required', 'string', 'max:80'],
            'why_2_text' => ['required', 'string', 'max:500'],
            'why_3_title' => ['required', 'string', 'max:80'],
            'why_3_text' => ['required', 'string', 'max:500'],
            'why_4_title' => ['required', 'string', 'max:80'],
            'why_4_text' => ['required', 'string', 'max:500'],

            'programs_heading' => ['required', 'string', 'max:160'],
            'preschool_text' => ['required', 'string', 'max:500'],
            'elementary_text' => ['required', 'string', 'max:500'],
            'junior_high_text' => ['required', 'string', 'max:500'],

            'updates_heading' => ['required', 'string', 'max:160'],
            'updates_intro' => ['required', 'string', 'max:600'],
            'life_heading' => ['required', 'string', 'max:180'],

            'cta_heading' => ['required', 'string', 'max:180'],
            'cta_text' => ['required', 'string', 'max:600'],
            'cta_button' => ['required', 'string', 'max:40'],

            'footer_tagline' => ['required', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email:rfc', 'max:150'],
            'contact_address' => ['nullable', 'string', 'max:300'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:500'],

            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        // The authorization checkbox is required only when a NEW hero image
        // is actually uploaded. The "accepted" rule is intentionally not
        // attached to an absent field because accepted is an implicit rule.
        $rules['hero_image_authorized'] = $request->hasFile('hero_image')
            ? ['required', 'accepted']
            : ['nullable'];

        $validated = $request->validate($rules, [
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

        return back()->with('success', 'Homepage content saved. Open the public homepage to review the changes.');
    }
}