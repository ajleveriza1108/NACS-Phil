<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\AdmissionsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionsContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.admissions-content.edit', [
            'content' => SiteContent::valuesFor('admissions', AdmissionsContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['required', 'string', 'max:80'],
            'hero_heading' => ['required', 'string', 'max:160'],
            'hero_highlight' => ['required', 'string', 'max:160'],
            'hero_lead' => ['required', 'string', 'max:1000'],

            'welcome_heading' => ['required', 'string', 'max:180'],
            'welcome_text' => ['required', 'string', 'max:1800'],

            'step_1_title' => ['required', 'string', 'max:100'],
            'step_1_text' => ['required', 'string', 'max:1200'],
            'step_2_title' => ['required', 'string', 'max:100'],
            'step_2_text' => ['required', 'string', 'max:1200'],
            'step_3_title' => ['required', 'string', 'max:100'],
            'step_3_text' => ['required', 'string', 'max:1200'],
            'step_4_title' => ['required', 'string', 'max:100'],
            'step_4_text' => ['required', 'string', 'max:1200'],

            'requirements_heading' => ['required', 'string', 'max:180'],
            'requirements_intro' => ['required', 'string', 'max:1800'],
            'requirement_1_title' => ['required', 'string', 'max:120'],
            'requirement_1_text' => ['required', 'string', 'max:1500'],
            'requirement_2_title' => ['required', 'string', 'max:120'],
            'requirement_2_text' => ['required', 'string', 'max:1500'],
            'requirement_3_title' => ['required', 'string', 'max:120'],
            'requirement_3_text' => ['required', 'string', 'max:1500'],
            'requirement_4_title' => ['required', 'string', 'max:120'],
            'requirement_4_text' => ['required', 'string', 'max:1500'],
            'requirements_note' => ['required', 'string', 'max:1800'],

            'dates_heading' => ['required', 'string', 'max:180'],
            'dates_text' => ['required', 'string', 'max:2500'],
            'school_year_label' => ['required', 'string', 'max:80'],
            'school_year_value' => ['required', 'string', 'max:160'],
            'status_label' => ['required', 'string', 'max:80'],
            'status_value' => ['required', 'string', 'max:220'],

            'faq_heading' => ['required', 'string', 'max:180'],
            'faq_1_q' => ['required', 'string', 'max:220'],
            'faq_1_a' => ['required', 'string', 'max:1800'],
            'faq_2_q' => ['required', 'string', 'max:220'],
            'faq_2_a' => ['required', 'string', 'max:1800'],
            'faq_3_q' => ['required', 'string', 'max:220'],
            'faq_3_a' => ['required', 'string', 'max:1800'],
            'faq_4_q' => ['required', 'string', 'max:220'],
            'faq_4_a' => ['required', 'string', 'max:1800'],

            'privacy_heading' => ['required', 'string', 'max:180'],
            'privacy_text' => ['required', 'string', 'max:2000'],

            'cta_heading' => ['required', 'string', 'max:180'],
            'cta_text' => ['required', 'string', 'max:1500'],
            'cta_primary_button' => ['required', 'string', 'max:40'],
            'cta_secondary_button' => ['required', 'string', 'max:40'],
        ]);

        SiteContent::storeValues('admissions', $validated);

        return back()->with('success', 'Admissions page content saved. Use Preview Admissions Page to review the public result.');
    }
}