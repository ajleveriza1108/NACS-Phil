<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\NewsContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.news-content.edit', [
            'content' => SiteContent::valuesFor('news', NewsContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['required', 'string', 'max:100'],
            'hero_heading' => ['required', 'string', 'max:160'],
            'hero_highlight' => ['required', 'string', 'max:160'],
            'hero_lead' => ['required', 'string', 'max:1000'],
            'listing_heading' => ['required', 'string', 'max:180'],
            'listing_text' => ['required', 'string', 'max:1800'],
            'empty_heading' => ['required', 'string', 'max:180'],
            'empty_text' => ['required', 'string', 'max:1200'],
            'detail_back_label' => ['required', 'string', 'max:60'],
            'detail_footer_heading' => ['required', 'string', 'max:180'],
            'detail_footer_text' => ['required', 'string', 'max:1500'],
            'detail_contact_button' => ['required', 'string', 'max:40'],
        ]);

        SiteContent::storeValues('news', $validated);

        return back()->with('success', 'News page settings saved. Existing announcements were not changed.');
    }
}