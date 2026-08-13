<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoSettingController extends Controller
{
    public const PAGES = [
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'admissions' => 'Admissions',
        'faculty.index' => 'Faculty & Staff',
        'documents.index' => 'Documents',
        'calendar.index' => 'Academic Calendar',
        'announcements.index' => 'News',
        'events.index' => 'Events',
        'gallery.index' => 'Gallery',
        'contact' => 'Contact',
        'privacy' => 'Privacy',
    ];

    public function edit(): View
    {
        return view('admin.seo.edit', [
            'pages' => self::PAGES,
            'settings' => SeoSetting::query()->whereIn('page_key', array_keys(self::PAGES))->get()->keyBy('page_key'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pages' => ['required','array'],
            'pages.*.title' => ['nullable','string','max:180'],
            'pages.*.meta_description' => ['nullable','string','max:320'],
            'pages.*.social_title' => ['nullable','string','max:180'],
            'pages.*.social_description' => ['nullable','string','max:320'],
            'pages.*.canonical_url' => ['nullable','url:http,https','max:500'],
            'pages.*.no_index' => ['nullable','boolean'],
        ]);

        foreach (self::PAGES as $key => $label) {
            $data = $validated['pages'][$key] ?? [];

            SeoSetting::query()->updateOrCreate(
                ['page_key' => $key],
                [
                    'title' => $data['title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'social_title' => $data['social_title'] ?? null,
                    'social_description' => $data['social_description'] ?? null,
                    'canonical_url' => $data['canonical_url'] ?? null,
                    'no_index' => (bool) ($data['no_index'] ?? false),
                    'updated_by_user_id' => $request->user()?->id,
                ]
            );
        }

        return back()->with('success', 'SEO and social-sharing settings saved.');
    }
}
