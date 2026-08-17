<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\SecurityEventLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        $request->session()->regenerate();
        // Phase 51: rotate the CSRF token after the password transition.
        $request->session()->regenerateToken();

        app(SecurityEventLogger::class)->record($request, 'auth.password.changed', 'notice', [
            'action' => 'portal_password_change',
        ]);

        return redirect()->route('portal.dashboard')->with('success', 'Password updated. Other database-backed sessions were revoked.');
    }
}
