<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();

            // Log activity trail (spatie activity log or custom logger)
            Log::info('NIC Authentication Success: User ' . Auth::user()->email . ' signed in.');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our government records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $email = Auth::user() ? Auth::user()->email : 'Unknown';
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('NIC Session Terminated: User ' . $email . ' logged out.');

        return redirect()->route('login');
    }
}
