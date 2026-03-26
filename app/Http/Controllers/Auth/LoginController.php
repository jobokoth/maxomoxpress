<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectPath(Auth::user()));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->redirectPath($request->user()));
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            return back()
                ->withErrors(['email' => 'The provided credentials are invalid.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath($request->user()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectPath(?User $user): string
    {
        $school = $user?->schools()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('school_user.left_at')
                    ->orWhere('school_user.left_at', '>', now());
            })
            ->orderByDesc('school_user.is_primary_school')
            ->first();

        if (! $school instanceof School) {
            Auth::logout();

            return route('login');
        }

        if ($user?->can('parent-portal.view')) {
            return route('tenant.parent-portal.index', ['school_slug' => $school->slug]);
        }

        return route('tenant.dashboard', ['school_slug' => $school->slug]);
    }
}
