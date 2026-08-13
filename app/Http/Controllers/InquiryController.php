<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guardian_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:40'],
            'student_name' => ['nullable', 'string', 'max:100'],
            'level_interested' => ['required', 'in:Preschool,Elementary,Junior High School,General Inquiry'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy_consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);

        unset($validated['privacy_consent'], $validated['website']);

        Inquiry::create($validated + [
            'status' => 'new',
            'privacy_consent_at' => now(),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        return back()->with('success', 'Thank you. Your inquiry has been received by NACS-Phil.');
    }
}
