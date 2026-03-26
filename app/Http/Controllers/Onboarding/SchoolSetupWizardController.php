<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PlatformSetting;
use App\Models\School;
use App\Models\Term;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolSetupWizardController extends Controller
{
    /**
     * Show the current wizard step.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $school = $this->resolveSchool($request);

        if ($school === null) {
            return redirect()->route('school.register')
                ->with('info', 'Please register your school first.');
        }

        if ($school->isOnboardingComplete()) {
            return redirect()->route('tenant.dashboard', $school->slug);
        }

        $step = max(1, $school->onboarding_step + 1);
        // If they already completed step 5, show completion
        if ($step > 5) {
            $step = 5;
        }

        return view('onboarding.wizard', compact('school', 'step'));
    }

    /**
     * Process a wizard step submission.
     */
    public function step(Request $request): RedirectResponse
    {
        $school = $this->resolveSchool($request);

        if ($school === null) {
            return redirect()->route('school.register');
        }

        $step = (int) $request->input('step');

        return match ($step) {
            1 => $this->processStep1($request, $school),
            2 => $this->processStep2($request, $school),
            3 => $this->processStep3($request, $school),
            4 => $this->processStep4($request, $school),
            5 => $this->processStep5($request, $school),
            default => redirect()->route('onboarding'),
        };
    }

    /**
     * Step 1: School Info.
     */
    private function processStep1(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                'unique:schools,slug,'.$school->id,
            ],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:12',
            'website' => 'nullable|url|max:255',
        ]);

        $school->update(array_merge($data, ['onboarding_step' => 1]));

        return redirect()->route('onboarding')->with('success', 'School information saved.');
    }

    /**
     * Step 2: Academic Structure.
     */
    private function processStep2(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_name' => 'required|string|max:100',
            'academic_year_start' => 'required|date',
            'academic_year_end' => 'required|date|after:academic_year_start',
            'number_of_terms' => 'required|in:2,3',
            'term_names' => 'required|array',
            'term_names.*' => 'required|string|max:100',
            'term_starts' => 'required|array',
            'term_starts.*' => 'required|date',
            'term_ends' => 'required|array',
            'term_ends.*' => 'required|date',
        ]);

        DB::transaction(function () use ($data, $school): void {
            // Mark any existing academic years as non-current
            AcademicYear::withoutGlobalScopes()->where('school_id', $school->id)
                ->update(['is_current' => false]);

            $academicYear = AcademicYear::withoutGlobalScopes()->create([
                'school_id' => $school->id,
                'name' => $data['academic_year_name'],
                'start_date' => $data['academic_year_start'],
                'end_date' => $data['academic_year_end'],
                'is_current' => true,
            ]);

            // Mark any existing terms as non-current
            Term::withoutGlobalScopes()->where('school_id', $school->id)
                ->update(['is_current' => false]);

            $termCount = (int) $data['number_of_terms'];
            for ($i = 0; $i < $termCount; $i++) {
                Term::withoutGlobalScopes()->create([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'name' => $data['term_names'][$i],
                    'start_date' => $data['term_starts'][$i],
                    'end_date' => $data['term_ends'][$i],
                    'is_current' => $i === 0,
                ]);
            }
        });

        $school->update(['onboarding_step' => 2]);

        return redirect()->route('onboarding')->with('success', 'Academic structure saved.');
    }

    /**
     * Step 3: First Admin User.
     */
    private function processStep3(Request $request, School $school): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $user->update(array_filter([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]));

        // Ensure user is attached to this school
        $pivotExists = DB::table('school_user')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $pivotExists) {
            $school->users()->attach($user->id, [
                'role_in_school' => 'school_admin',
                'is_primary_school' => true,
                'joined_at' => now(),
            ]);
        }

        // Ensure school_admin role exists with all permissions, then assign
        $role = SchoolRegistrationController::ensureSchoolAdminRole();
        if (! $user->hasRole('school_admin')) {
            $user->assignRole($role);
        }

        $school->update(['onboarding_step' => 3]);

        return redirect()->route('onboarding')->with('success', 'Admin user confirmed.');
    }

    /**
     * Step 4: Payment Setup (skippable).
     */
    private function processStep4(Request $request, School $school): RedirectResponse
    {
        if ($request->boolean('skip')) {
            $school->update(['onboarding_step' => 4]);

            return redirect()->route('onboarding')->with('info', 'Payment setup skipped. You can configure it later in Settings.');
        }

        $data = $request->validate([
            'mpesa_paybill' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'accept_cheques' => 'nullable|boolean',
        ]);

        $settings = $school->settings ?? [];
        $settings['payment'] = [
            'mpesa_paybill' => $data['mpesa_paybill'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'accept_cheques' => $request->boolean('accept_cheques'),
        ];

        $school->update([
            'settings' => $settings,
            'onboarding_step' => 4,
        ]);

        return redirect()->route('onboarding')->with('success', 'Payment settings saved.');
    }

    /**
     * Step 5: Branding (skippable).
     */
    private function processStep5(Request $request, School $school): RedirectResponse
    {
        if ($request->boolean('skip')) {
            return $this->completeOnboarding($school);
        }

        $request->validate([
            'logo' => 'nullable|image|max:2048',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $updates = ['onboarding_step' => 5];

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $updates['logo'] = $this->uploadToCloudinaryOrLocal($logo, 'logos');
        }

        if ($request->hasFile('cover_image')) {
            $cover = $request->file('cover_image');
            $updates['cover_image'] = $this->uploadToCloudinaryOrLocal($cover, 'covers');
        }

        $school->update($updates);

        return $this->completeOnboarding($school);
    }

    /**
     * Finalize onboarding, set trial, and redirect.
     */
    private function completeOnboarding(School $school): RedirectResponse
    {
        $updates = [
            'onboarding_completed_at' => now(),
            'onboarding_step' => 5,
        ];

        if (PlatformSetting::get('trial_enabled', true)) {
            $trialDays = PlatformSetting::get('trial_days', 30);
            $updates['trial_ends_at'] = now()->addDays($trialDays);
        }

        $school->update($updates);

        return redirect()->route('tenant.dashboard', $school->slug)
            ->with('success', 'Setup complete! Welcome to '.$school->name.'.');
    }

    /**
     * Upload a file to Cloudinary if CloudinaryService exists, otherwise store locally.
     * Returns the public_id (Cloudinary) or local path.
     */
    private function uploadToCloudinaryOrLocal(\Illuminate\Http\UploadedFile $file, string $folder): string
    {
        if (class_exists(\App\Services\CloudinaryService::class)) {
            $service = app(\App\Services\CloudinaryService::class);

            return $service->upload($file->getRealPath(), $folder);
        }

        // Fallback: local storage, return path as public_id equivalent
        return $file->store($folder, 'public');
    }

    /**
     * Resolve the school for the currently authenticated user.
     */
    private function resolveSchool(Request $request): ?School
    {
        return Auth::user()->schools()
            ->whereNull('onboarding_completed_at')
            ->latest('school_user.created_at')
            ->first();
    }
}
