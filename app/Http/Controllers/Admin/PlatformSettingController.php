<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->check() || ! auth()->user()->hasRole('super_admin')) {
                abort(403, 'Super admin access required.');
            }

            return $next($request);
        });
    }

    public function index(): View
    {
        $settings = PlatformSetting::orderBy('key')->paginate(20);

        return view('admin.platform-settings.index', compact('settings'));
    }

    public function create(): View
    {
        return view('admin.platform-settings.form', [
            'setting' => new PlatformSetting,
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => 'required|string|max:100|unique:platform_settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:255',
        ]);

        PlatformSetting::create($data);

        return redirect()->route('admin.platform-settings.index')
            ->with('success', "Setting '{$data['key']}' created.");
    }

    public function edit(PlatformSetting $platformSetting): View
    {
        return view('admin.platform-settings.form', [
            'setting' => $platformSetting,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, PlatformSetting $platformSetting): RedirectResponse
    {
        $data = $request->validate([
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string|max:255',
        ]);

        $platformSetting->update($data);

        return redirect()->route('admin.platform-settings.index')
            ->with('success', "Setting '{$platformSetting->key}' updated.");
    }

    public function destroy(PlatformSetting $platformSetting): RedirectResponse
    {
        $key = $platformSetting->key;
        $platformSetting->delete();

        return redirect()->route('admin.platform-settings.index')
            ->with('success', "Setting '{$key}' deleted.");
    }
}
