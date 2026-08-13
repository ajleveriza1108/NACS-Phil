<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Support\ContactContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.contact-content.edit', [
            'content' => SiteContent::valuesFor('contact', ContactContent::defaults()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_badge' => ['required','string','max:100'],
            'hero_heading' => ['required','string','max:160'],
            'hero_highlight' => ['required','string','max:160'],
            'hero_lead' => ['required','string','max:1200'],
            'office_heading' => ['required','string','max:180'],
            'office_text' => ['required','string','max:1800'],
            'address' => ['required','string','max:500'],
            'phone' => ['nullable','string','max:80'],
            'email' => ['nullable','email:rfc','max:150'],
            'facebook_url' => ['nullable','url:http,https','max:500'],
            'office_hours' => ['nullable','string','max:300'],
            'inquiry_heading' => ['required','string','max:180'],
            'inquiry_text' => ['required','string','max:1800'],
            'privacy_heading' => ['required','string','max:180'],
            'privacy_text' => ['required','string','max:1800'],
        ]);

        SiteContent::storeValues('contact', $validated);

        return back()->with('success', 'Contact page settings saved. Existing inquiries were not changed.');
    }
}