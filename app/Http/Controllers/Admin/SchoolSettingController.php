<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    public const FIELDS = [
        'school_name' => ['group'=>'identity','label'=>'School Name'],
        'short_name' => ['group'=>'identity','label'=>'Short Name'],
        'tagline' => ['group'=>'identity','label'=>'Tagline'],
        'current_school_year' => ['group'=>'identity','label'=>'Current School Year'],
        'address' => ['group'=>'contact','label'=>'Address'],
        'phone' => ['group'=>'contact','label'=>'Phone'],
        'email' => ['group'=>'contact','label'=>'Email'],
        'office_hours' => ['group'=>'contact','label'=>'Office Hours'],
        'facebook_url' => ['group'=>'contact','label'=>'Facebook URL'],
        'privacy_email' => ['group'=>'privacy','label'=>'Privacy Contact Email'],
        'emergency_banner' => ['group'=>'website','label'=>'Emergency / Important Banner'],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'fields' => self::FIELDS,
            'values' => SchoolSetting::allValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [];

        foreach (array_keys(self::FIELDS) as $key) {
            $rules[$key] = ['nullable','string','max:1000'];
        }

        $data = $request->validate($rules);

        foreach (self::FIELDS as $key => $meta) {
            SchoolSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => trim((string) ($data[$key] ?? '')),
                    'group' => $meta['group'],
                    'is_public' => true,
                    'updated_by_user_id' => $request->user()?->id,
                ]
            );
        }

        return back()->with('success', 'School settings saved. Production credentials were not changed.');
    }
}
