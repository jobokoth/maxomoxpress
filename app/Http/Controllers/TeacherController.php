<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TeacherController extends Controller
{
    private function resolveTeacher(School $school, string $id): User
    {
        return $school->users()
            ->wherePivot('role_in_school', 'teacher')
            ->where('users.id', $id)
            ->firstOrFail();
    }

    private function loadTeacherProfile(User $teacher, School $school): User
    {
        $teacher->load([
            'subjectAssignments' => fn ($q) => $q->where('school_id', $school->id)
                ->with(['subject', 'course', 'batch', 'academicYear', 'term']),
        ]);

        return $teacher;
    }

    public function index(Request $request): View
    {
        $school = app('current_school');

        $teachers = $school->users()
            ->wherePivot('role_in_school', 'teacher')
            ->orderBy('name')
            ->get();

        return view('teachers.index', compact('school', 'teachers'));
    }

    public function create(Request $request): View
    {
        $school = app('current_school');

        return view('teachers.create', compact('school'));
    }

    public function show(Request $request, string $teacher): View
    {
        $school = app('current_school');
        $teacher = $this->loadTeacherProfile($this->resolveTeacher($school, $teacher), $school);

        return view('teachers.show', compact('school', 'teacher'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = app('current_school');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function () use ($data, $school): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $school->users()->attach($user->id, [
                'role_in_school' => 'teacher',
                'is_primary_school' => true,
                'joined_at' => now(),
            ]);

            $role = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);

            $teacherPermissions = ['dashboard.view', 'attendance.manage', 'timetable.manage', 'marks.manage', 'announcements.view'];
            $permissions = collect($teacherPermissions)
                ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
            $role->syncPermissions($permissions);

            $user->assignRole($role);
        });

        return redirect()
            ->route('tenant.teachers.index', $school->slug)
            ->with('success', 'Teacher account created successfully.');
    }

    public function edit(Request $request, string $teacher): View
    {
        $school = app('current_school');
        $teacher = $this->loadTeacherProfile($this->resolveTeacher($school, $teacher), $school);

        return view('teachers.edit', compact('school', 'teacher'));
    }

    public function update(Request $request, string $teacher): RedirectResponse
    {
        $school = app('current_school');
        $teacher = $this->resolveTeacher($school, $teacher);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $teacher->update(array_filter([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
        ]));

        return redirect()
            ->route('tenant.teachers.edit', ['school_slug' => $school->slug, 'teacher' => $teacher->id])
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Request $request, string $teacher): RedirectResponse
    {
        $school = app('current_school');
        $teacher = $this->resolveTeacher($school, $teacher);

        $school->users()->detach($teacher->id);

        return redirect()
            ->route('tenant.teachers.index', $school->slug)
            ->with('success', "{$teacher->name} has been removed from this school.");
    }
}
