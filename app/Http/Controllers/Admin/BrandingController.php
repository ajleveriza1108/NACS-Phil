<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('admin.branding.edit', [
            'logoUrl' => SchoolSetting::logoUrl(),
            'logoAlt' => SchoolSetting::logoAlt(),
            'hasOfficialLogo' => SchoolSetting::officialBrandingApproved(),
            'approvedAt' => SchoolSetting::valueFor('official_branding_approved_at'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'official_logo' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
                'dimensions:min_width=128,min_height=128,max_width=4000,max_height=4000',
            ],
            'official_logo_alt' => ['required','string','max:160'],
            'official_branding_approved' => ['accepted'],
        ], [
            'official_branding_approved.accepted' => 'Confirm that this logo is officially approved for public school use.',
        ]);

        $oldPath = SchoolSetting::valueFor('official_logo_path');
        $path = $request->file('official_logo')->store('branding', 'public');

        if (! is_string($path) || ! str_starts_with($path, 'branding/')) {
            throw new \RuntimeException('The official logo could not be stored safely.');
        }

        $this->saveSetting('official_logo_path', $path, 'branding', true, $request);
        $this->saveSetting('official_logo_alt', trim($validated['official_logo_alt']), 'branding', true, $request);
        $this->saveSetting('official_branding_approved_at', now()->toIso8601String(), 'branding', false, $request);
        $this->saveSetting('official_branding_approved_by_user_id', (string) $request->user()->id, 'branding', false, $request);

        if (
            filled($oldPath)
            && $oldPath !== $path
            && str_starts_with((string) $oldPath, 'branding/')
        ) {
            Storage::disk('public')->delete((string) $oldPath);
        }

        return back()->with('success', 'Official school branding saved and activated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $oldPath = SchoolSetting::valueFor('official_logo_path');

        if (filled($oldPath) && str_starts_with((string) $oldPath, 'branding/')) {
            Storage::disk('public')->delete((string) $oldPath);
        }

        foreach ([
            'official_logo_path',
            'official_branding_approved_at',
            'official_branding_approved_by_user_id',
        ] as $key) {
            SchoolSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => '',
                    'group' => 'branding',
                    'is_public' => false,
                    'updated_by_user_id' => $request->user()?->id,
                ]
            );
        }

        return back()->with('success', 'Official logo removed. The development mark is active again until an approved logo is uploaded.');
    }

    private function saveSetting(
        string $key,
        string $value,
        string $group,
        bool $isPublic,
        Request $request
    ): void {
        SchoolSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'is_public' => $isPublic,
                'updated_by_user_id' => $request->user()?->id,
            ]
        );
    }
}
