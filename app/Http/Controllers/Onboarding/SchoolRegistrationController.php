<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SchoolRegistrationController extends Controller
{
    /**
     * All permissions a school_admin should have.
     * Keep this list in sync with the sidebar in layouts/dashui.blade.php.
     */
    public const SCHOOL_ADMIN_PERMISSIONS = [
        'dashboard.view',
        'students.view',
        'students.manage',
        'academic-years.manage',
        'terms.manage',
        'departments.manage',
        'courses.manage',
        'batches.manage',
        'subjects.view',
        'subjects.manage',
        'teachers.manage',
        'teacher-assignments.manage',
        'attendance.manage',
        'timetable.manage',
        'announcements.view',
        'announcements.manage',
        'assessments.manage',
        'marks.manage',
        'reports.view',
        'fees.manage',
        'student-services.manage',
        'communications.manage',
        'parent-portal.view',
    ];

    public function show(): View
    {
        return view('auth.register-school');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'your_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'school_name' => 'required|string|max:255',
        ]);

        $slug = $this->generateUniqueSlug($data['school_name']);

        DB::transaction(function () use ($data, $slug): void {
            // 1. Create User
            $user = User::create([
                'name' => $data['your_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // 2. Create School
            $school = School::create([
                'name' => $data['school_name'],
                'slug' => $slug,
                'is_active' => true,
                'is_trial' => true,
                'onboarding_step' => 0,
                'timezone' => 'Africa/Nairobi',
                'currency' => 'KES',
                'country' => 'Kenya',
            ]);

            // 3. Attach user to school via pivot
            $school->users()->attach($user->id, [
                'role_in_school' => 'school_admin',
                'is_primary_school' => true,
                'joined_at' => now(),
            ]);

            // 4. Ensure the school_admin Spatie role exists with all permissions
            $role = self::ensureSchoolAdminRole();
            $user->assignRole($role);

            // 5. Log in
            Auth::login($user);
        });

        return redirect()->route('onboarding')
            ->with('success', 'Account created! Let\'s set up your school.');
    }

    /**
     * Get or create the school_admin role and ensure it has all required permissions.
     * Called from registration and wizard controllers.
     */
    public static function ensureSchoolAdminRole(): Role
    {
        $role = Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);

        // Create any missing permissions and sync them all onto the role
        $permissions = collect(self::SCHOOL_ADMIN_PERMISSIONS)
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));

        $role->syncPermissions($permissions);

        return $role;
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (School::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
