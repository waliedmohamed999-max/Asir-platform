<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (! $user?->is_active) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'تم تعطيل هذا الحساب مؤقتاً. يرجى التواصل مع الإدارة.',
            ]);
        }

        $target = match ($user?->role) {
            'super_admin', 'admin', 'event_manager', 'content_manager', 'finance', 'support' => route('admin.dashboard'),
            'organizer' => route('organizer.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->intended($target);
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
