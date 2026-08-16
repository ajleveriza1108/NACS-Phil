<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\AboutContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AboutContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.about-content.edit', [
            'content' => SiteContent::valuesFor('about', AboutContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'hero_badge' => ['required', 'string', 'max:80'],
            'hero_heading' => ['required', 'string', 'max:140'],
            'hero_highlight' => ['required', 'string', 'max:140'],
            'hero_lead' => ['required', 'string', 'max:700'],

            'story_kicker' => ['required', 'string', 'max:80'],
            'story_heading' => ['required', 'string', 'max:180'],
            'story_body' => ['required', 'string', 'max:5000'],
            'story_note' => ['required', 'string', 'max:1000'],

            'mission_title' => ['required', 'string', 'max:100'],
            'mission_text' => ['required', 'string', 'max:2500'],
            'vision_title' => ['required', 'string', 'max:100'],
            'vision_text' => ['required', 'string', 'max:2500'],

            'faith_kicker' => ['required', 'string', 'max:80'],
            'faith_heading' => ['required', 'string', 'max:180'],
            'faith_text' => ['required', 'string', 'max:3500'],
            'verse_text' => ['required', 'string', 'max:500'],
            'verse_reference' => ['required', 'string', 'max:100'],

            'values_heading' => ['required', 'string', 'max:180'],
            'value_1_title' => ['required', 'string', 'max:80'],
            'value_1_text' => ['required', 'string', 'max:1000'],
            'value_2_title' => ['required', 'string', 'max:80'],
            'value_2_text' => ['required', 'string', 'max:1000'],
            'value_3_title' => ['required', 'string', 'max:80'],
            'value_3_text' => ['required', 'string', 'max:1000'],
            'value_4_title' => ['required', 'string', 'max:80'],
            'value_4_text' => ['required', 'string', 'max:1000'],


            'distinctives_kicker' => ['required', 'string', 'max:80'],
            'distinctives_heading' => ['required', 'string', 'max:180'],
            'distinctives_intro' => ['required', 'string', 'max:1800'],
            'distinctive_1_title' => ['required', 'string', 'max:100'],
            'distinctive_1_text' => ['required', 'string', 'max:1200'],
            'distinctive_2_title' => ['required', 'string', 'max:100'],
            'distinctive_2_text' => ['required', 'string', 'max:1200'],
            'distinctive_3_title' => ['required', 'string', 'max:100'],
            'distinctive_3_text' => ['required', 'string', 'max:1200'],
            'distinctive_4_title' => ['required', 'string', 'max:100'],
            'distinctive_4_text' => ['required', 'string', 'max:1200'],
            'distinctives_note' => ['required', 'string', 'max:1200'],
            'leadership_kicker' => ['required', 'string', 'max:80'],
            'leadership_heading' => ['required', 'string', 'max:180'],
            'leader_name' => ['nullable', 'string', 'max:120'],
            'leader_role' => ['required', 'string', 'max:120'],
            'leader_message' => ['required', 'string', 'max:5000'],

            'community_heading' => ['required', 'string', 'max:180'],
            'community_text' => ['required', 'string', 'max:2000'],

            'cta_heading' => ['required', 'string', 'max:180'],
            'cta_text' => ['required', 'string', 'max:1500'],
            'cta_programs_button' => ['required', 'string', 'max:40'],
            'cta_contact_button' => ['required', 'string', 'max:40'],

            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'leadership_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];

        foreach (['hero', 'leadership'] as $slot) {
            $rules[$slot.'_image_authorized'] = $request->hasFile($slot.'_image')
                ? ['required', 'accepted']
                : ['nullable'];
        }

        $validated = $request->validate($rules, [
            'hero_image_authorized.required' => 'Confirm that the new About page hero photograph is approved for website publication.',
            'hero_image_authorized.accepted' => 'Confirm that the new About page hero photograph is approved for website publication.',
            'leadership_image_authorized.required' => 'Confirm that the new leadership photograph is approved for website publication.',
            'leadership_image_authorized.accepted' => 'Confirm that the new leadership photograph is approved for website publication.',
        ]);

        unset(
            $validated['hero_image'],
            $validated['hero_image_authorized'],
            $validated['leadership_image'],
            $validated['leadership_image_authorized'],
        );

        $current = SiteContent::valuesFor('about', AboutContent::defaults());

        foreach (['hero', 'leadership'] as $slot) {
            $input = $slot.'_image';
            $pathKey = $slot.'_image_path';

            if (! $request->hasFile($input)) {
                continue;
            }

            $newPath = $request->file($input)->store('site/about', 'public');
            $oldPath = (string) ($current[$pathKey] ?? '');
            $validated[$pathKey] = $newPath;

            if ($oldPath !== '' && $oldPath !== $newPath && str_starts_with($oldPath, 'site/about/')) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        SiteContent::storeValues('about', $validated);

        return back()->with('success', 'About page content saved. Use Preview About Page to review the public result.');
    }
}
