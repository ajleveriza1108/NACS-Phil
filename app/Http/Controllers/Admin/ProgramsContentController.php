<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\ProgramsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramsContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.programs-content.edit', [
            'content' => SiteContent::valuesFor('programs', ProgramsContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['required', 'string', 'max:80'],
            'hero_heading' => ['required', 'string', 'max:160'],
            'hero_highlight' => ['required', 'string', 'max:160'],
            'hero_lead' => ['required', 'string', 'max:900'],

            'overview_heading' => ['required', 'string', 'max:180'],
            'overview_text' => ['required', 'string', 'max:1500'],

            'preschool_kicker' => ['required', 'string', 'max:80'],
            'preschool_title' => ['required', 'string', 'max:100'],
            'preschool_levels' => ['required', 'string', 'max:180'],
            'preschool_text' => ['required', 'string', 'max:2500'],
            'preschool_feature_1' => ['required', 'string', 'max:160'],
            'preschool_feature_2' => ['required', 'string', 'max:160'],
            'preschool_feature_3' => ['required', 'string', 'max:160'],
            'preschool_feature_4' => ['required', 'string', 'max:160'],

            'elementary_kicker' => ['required', 'string', 'max:80'],
            'elementary_title' => ['required', 'string', 'max:100'],
            'elementary_levels' => ['required', 'string', 'max:180'],
            'elementary_text' => ['required', 'string', 'max:2500'],
            'elementary_feature_1' => ['required', 'string', 'max:160'],
            'elementary_feature_2' => ['required', 'string', 'max:160'],
            'elementary_feature_3' => ['required', 'string', 'max:160'],
            'elementary_feature_4' => ['required', 'string', 'max:160'],

            'junior_kicker' => ['required', 'string', 'max:80'],
            'junior_title' => ['required', 'string', 'max:100'],
            'junior_levels' => ['required', 'string', 'max:180'],
            'junior_text' => ['required', 'string', 'max:2500'],
            'junior_feature_1' => ['required', 'string', 'max:160'],
            'junior_feature_2' => ['required', 'string', 'max:160'],
            'junior_feature_3' => ['required', 'string', 'max:160'],
            'junior_feature_4' => ['required', 'string', 'max:160'],

            'approach_kicker' => ['required', 'string', 'max:80'],
            'approach_heading' => ['required', 'string', 'max:180'],
            'approach_text' => ['required', 'string', 'max:1800'],
            'approach_1_title' => ['required', 'string', 'max:80'],
            'approach_1_text' => ['required', 'string', 'max:1000'],
            'approach_2_title' => ['required', 'string', 'max:80'],
            'approach_2_text' => ['required', 'string', 'max:1000'],
            'approach_3_title' => ['required', 'string', 'max:80'],
            'approach_3_text' => ['required', 'string', 'max:1000'],

            'faith_heading' => ['required', 'string', 'max:200'],
            'faith_text' => ['required', 'string', 'max:2500'],
            'verse_text' => ['required', 'string', 'max:500'],
            'verse_reference' => ['required', 'string', 'max:100'],

            'cta_heading' => ['required', 'string', 'max:180'],
            'cta_text' => ['required', 'string', 'max:1500'],
            'cta_admissions_button' => ['required', 'string', 'max:40'],
            'cta_contact_button' => ['required', 'string', 'max:40'],
        ]);

        SiteContent::storeValues('programs', $validated);

        return back()->with('success', 'Programs page content saved. Use Preview Programs Page to review the public result.');
    }
}