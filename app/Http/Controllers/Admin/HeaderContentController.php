<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class HeaderContentController extends Controller
{
    public const FIELDS = [
        'header_short_name' => ['label' => 'Header Short Name', 'max' => 80],
        'header_school_name' => ['label' => 'Header Full School Name', 'max' => 160],
        'header_nav_home' => ['label' => 'Home Label', 'max' => 32],
        'header_nav_about' => ['label' => 'About Label', 'max' => 32],
        'header_nav_programs' => ['label' => 'Programs Label', 'max' => 32],
        'header_nav_admissions' => ['label' => 'Admissions Label', 'max' => 32],
        'header_nav_news' => ['label' => 'News Label', 'max' => 32],
        'header_nav_events' => ['label' => 'Events Label', 'max' => 32],
        'header_nav_gallery' => ['label' => 'Gallery Label', 'max' => 32],
        'header_nav_contact' => ['label' => 'Contact Label', 'max' => 32],
        'header_resources_label' => ['label' => 'Resources Label', 'max' => 32],
        'header_mobile_academics_label' => ['label' => 'Mobile Academics Group', 'max' => 32],
        'header_mobile_media_label' => ['label' => 'Mobile News & Media Group', 'max' => 32],
        'header_enroll_label' => ['label' => 'Enrollment Button', 'max' => 40],
        'document_watermark' => ['label' => 'Academic PDF Watermark', 'max' => 50],
    ];

    public function edit(): View
    {
        return view('admin.header-content.edit', [
            'values' => $this->values(),
            'fields' => self::FIELDS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];

        foreach (self::FIELDS as $key => $meta) {
            $rules[$key] = ['nullable', 'string', 'max:'.$meta['max']];
        }

        $data = $request->validate($rules);

        foreach (self::FIELDS as $key => $meta) {
            SchoolSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => trim((string) ($data[$key] ?? '')),
                    'group' => 'header',
                    'is_public' => true,
                    'updated_by_user_id' => $request->user()?->id,
                ]
            );
        }

        return back()->with('success', 'Website header, navigation labels, and academic PDF watermark saved.');
    }

    private function values(): array
    {
        return [
            'header_short_name' => SchoolSetting::valueFor('header_short_name', SchoolSetting::valueFor('short_name', config('nacs.short_name'))),
            'header_school_name' => SchoolSetting::valueFor('header_school_name', SchoolSetting::valueFor('school_name', 'Noel Academy Christian of Sariaya Philippines, Inc.')),
            'header_nav_home' => SchoolSetting::valueFor('header_nav_home', 'Home'),
            'header_nav_about' => SchoolSetting::valueFor('header_nav_about', 'About'),
            'header_nav_programs' => SchoolSetting::valueFor('header_nav_programs', 'Programs'),
            'header_nav_admissions' => SchoolSetting::valueFor('header_nav_admissions', 'Admissions'),
            'header_nav_news' => SchoolSetting::valueFor('header_nav_news', 'News'),
            'header_nav_events' => SchoolSetting::valueFor('header_nav_events', 'Events'),
            'header_nav_gallery' => SchoolSetting::valueFor('header_nav_gallery', 'Gallery'),
            'header_nav_contact' => SchoolSetting::valueFor('header_nav_contact', 'Contact'),
            'header_resources_label' => SchoolSetting::valueFor('header_resources_label', 'Resources'),
            'header_mobile_academics_label' => SchoolSetting::valueFor('header_mobile_academics_label', 'Academics'),
            'header_mobile_media_label' => SchoolSetting::valueFor('header_mobile_media_label', 'News & Media'),
            'header_enroll_label' => SchoolSetting::valueFor('header_enroll_label', 'Enroll Now'),
            'document_watermark' => SchoolSetting::valueFor('document_watermark', SchoolSetting::valueFor('short_name', config('nacs.short_name'))),
        ];
    }
}
