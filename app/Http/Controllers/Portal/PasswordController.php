<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('portal.auth.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required','current_password'],
            'password' => ['required','confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
            'force_password_reset' => false,
        ])->save();

        return redirect()->route('portal.dashboard')->with('success', 'Password updated.');
    }
}
