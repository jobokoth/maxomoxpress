<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\CommunicationNotification;
use App\Models\Course;
use App\Models\Department;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\FeeAssignment;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\GradingScale;
use App\Models\Guardian;
use App\Models\SchoolEvent;
use App\Models\School;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentMark;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Term;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
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
        ])->map(fn (string $name): Permission => Permission::findOrCreate($name, 'web'));

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $staffRole = Role::findOrCreate('staff', 'web');
        $parentRole = Role::findOrCreate('parent', 'web');

        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions($permissions);
        $staffRole->syncPermissions([
            'dashboard.view',
            'students.view',
            'students.manage',
            'subjects.view',
            'attendance.manage',
            'timetable.manage',
            'announcements.view',
            'marks.manage',
            'reports.view',
            'fees.manage',
            'student-services.manage',
            'communications.manage',
        ]);
        $parentRole->syncPermissions([
            'parent-portal.view',
        ]);

        $school = School::query()->create([
            'name' => 'Masomo Demo School',
            'slug' => 'masomo-demo',
            'email' => 'admin@masomo.app',
            'phone' => '+254700000000',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'subscription_plan' => 'enterprise',
            'is_active' => true,
        ]);

        $admin = User::query()->create([
            'name' => 'Masomo Super Admin',
            'email' => 'admin@masomo.app',
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'M@s0m0#Adm1n2025!')),
            'email_verified_at' => now(),
            'phone' => '+254700000001',
        ]);
        $admin->assignRole('super-admin');

        $staff = User::factory()->count(12)->create();

        $school->users()->attach($admin->id, [
            'role_in_school' => 'super-admin',
            'is_primary_school' => true,
            'joined_at' => now(),
        ]);

        foreach ($staff as $member) {
            $school->users()->attach($member->id, [
                'role_in_school' => fake()->randomElement(['teacher', 'accountant', 'registrar']),
                'is_primary_school' => false,
                'joined_at' => now()->subDays(fake()->numberBetween(1, 90)),
            ]);
            $member->assignRole('staff');
        }

        app()->instance('current_school', $school);

        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-10',
            'is_current' => true,
        ]);

        $term = Term::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => '2025-01-06',
            'end_date' => '2025-04-12',
            'is_current' => true,
        ]);

        $departments = collect([
            ['name' => 'Sciences', 'code' => 'SCI'],
            ['name' => 'Humanities', 'code' => 'HUM'],
            ['name' => 'Business & ICT', 'code' => 'BICT'],
        ])->map(fn (array $department) => Department::query()->create([
            'school_id' => $school->id,
            'name' => $department['name'],
            'code' => $department['code'],
            'head_user_id' => $staff->random()->id,
        ]));

        $courses = collect([
            ['name' => 'Form 1', 'code' => 'F1', 'course_type' => 'secondary'],
            ['name' => 'Form 2', 'code' => 'F2', 'course_type' => 'secondary'],
            ['name' => 'Form 3', 'code' => 'F3', 'course_type' => 'secondary'],
            ['name' => 'Form 4', 'code' => 'F4', 'course_type' => 'secondary'],
        ])->map(function (array $course) use ($departments, $school): Course {
            return Course::query()->create([
                'school_id' => $school->id,
                'department_id' => $departments->random()->id,
                'name' => $course['name'],
                'code' => $course['code'],
                'course_type' => $course['course_type'],
                'duration_years' => 1,
            ]);
        });

        foreach ($courses as $course) {
            Batch::query()->create([
                'school_id' => $school->id,
                'course_id' => $course->id,
                'academic_year_id' => $year->id,
                'name' => $course->code . ' East',
                'capacity' => 45,
                'room_number' => fake()->numberBetween(100, 320),
            ]);
        }

        foreach (['Mathematics', 'Biology', 'Chemistry', 'History', 'Business Studies', 'Computer Science'] as $name) {
            Subject::query()->create([
                'school_id' => $school->id,
                'department_id' => $departments->random()->id,
                'name' => $name,
                'code' => strtoupper(substr($name, 0, 3)),
                'subject_type' => fake()->randomElement(['theory', 'practical', 'compulsory']),
                'credit_hours' => fake()->numberBetween(2, 6),
                'pass_mark' => 40,
                'max_mark' => 100,
            ]);
        }

        $sampleStudents = collect([
            ['first_name' => 'Amina', 'last_name' => 'Otieno', 'status' => 'applied'],
            ['first_name' => 'Brian', 'last_name' => 'Mwangi', 'status' => 'admitted'],
            ['first_name' => 'Cynthia', 'last_name' => 'Njeri', 'status' => 'enrolled'],
        ])->map(function (array $record) use ($school, $year, $courses): Student {
            $course = $courses->random();
            $batch = Batch::query()->where('course_id', $course->id)->inRandomOrder()->first();

            return Student::query()->create([
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'course_id' => $course->id,
                'batch_id' => $batch?->id,
                'first_name' => $record['first_name'],
                'last_name' => $record['last_name'],
                'gender' => fake()->randomElement(['male', 'female']),
                'date_of_birth' => fake()->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
                'admission_status' => $record['status'],
                'admission_date' => now()->subDays(30)->toDateString(),
                'enrollment_date' => $record['status'] === 'enrolled' ? now()->subDays(7)->toDateString() : null,
                'phone' => fake()->numerify('+2547########'),
                'city' => 'Nairobi',
                'country' => 'Kenya',
            ]);
        });

        foreach ($sampleStudents as $student) {
            $guardian = Guardian::query()->create([
                'school_id' => $school->id,
                'full_name' => fake()->name(),
                'phone' => fake()->numerify('+2547########'),
                'email' => fake()->safeEmail(),
                'relationship' => fake()->randomElement(['Mother', 'Father', 'Guardian']),
                'occupation' => fake()->jobTitle(),
                'address' => fake()->address(),
                'is_primary' => true,
            ]);

            $student->guardians()->attach($guardian->id, [
                'relationship' => $guardian->relationship,
                'is_primary_contact' => true,
            ]);
        }

        $firstGuardian = Guardian::query()->first();
        if ($firstGuardian) {
            $parentUser = User::query()->firstOrCreate(
                ['email' => 'parent@masomo.app'],
                [
                    'name' => $firstGuardian->full_name,
                    'password' => Hash::make('Parent@1234'),
                    'phone' => $firstGuardian->phone,
                ]
            );
            $parentUser->assignRole('parent');
            $school->users()->syncWithoutDetaching([
                $parentUser->id => [
                    'role_in_school' => 'parent',
                    'is_primary_school' => false,
                    'joined_at' => now(),
                ],
            ]);
            $firstGuardian->update(['user_id' => $parentUser->id, 'email' => $parentUser->email]);
        }

        $teacherPool = User::query()
            ->whereHas('schools', fn ($query) => $query->where('schools.id', $school->id))
            ->inRandomOrder()
            ->take(4)
            ->get();

        foreach (Batch::query()->take(3)->get() as $batch) {
            $subject = Subject::query()->inRandomOrder()->first();
            $teacher = $teacherPool->random();

            if (! $subject) {
                continue;
            }

            TimetableEntry::query()->create([
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'term_id' => $term->id,
                'course_id' => $batch->course_id,
                'batch_id' => $batch->id,
                'subject_id' => $subject->id,
                'teacher_user_id' => $teacher?->id,
                'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'start_time' => '08:00',
                'end_time' => '09:00',
                'room' => 'R-' . fake()->numberBetween(1, 12),
                'is_active' => true,
            ]);
        }

        foreach ($sampleStudents as $student) {
            StudentAttendance::query()->create([
                'school_id' => $school->id,
                'attendance_date' => now()->toDateString(),
                'student_id' => $student->id,
                'course_id' => $student->course_id,
                'batch_id' => $student->batch_id,
                'status' => fake()->randomElement(['present', 'present', 'present', 'late']),
                'marked_by_user_id' => $admin->id,
            ]);
        }

        foreach ($teacherPool as $teacher) {
            StaffAttendance::query()->create([
                'school_id' => $school->id,
                'attendance_date' => now()->toDateString(),
                'user_id' => $teacher->id,
                'status' => fake()->randomElement(['present', 'present', 'late']),
                'marked_by_user_id' => $admin->id,
            ]);
        }

        Announcement::query()->create([
            'school_id' => $school->id,
            'title' => 'Welcome to Term 1',
            'body' => 'All learners report by 7:30 AM. Ensure uniform compliance.',
            'audience' => 'all',
            'published_at' => now(),
            'is_pinned' => true,
            'created_by_user_id' => $admin->id,
        ]);

        $event = SchoolEvent::query()->create([
            'school_id' => $school->id,
            'title' => 'Parents Meeting',
            'description' => 'Term briefing with class teachers.',
            'event_type' => 'meeting',
            'start_at' => now()->addDays(5)->setTime(9, 0),
            'end_at' => now()->addDays(5)->setTime(12, 0),
            'location' => 'Main Hall',
            'audience' => 'parents',
            'created_by_user_id' => $admin->id,
        ]);

        CommunicationNotification::query()->create([
            'school_id' => $school->id,
            'title' => 'Welcome Parents',
            'message' => 'Parent portal is now available.',
            'channel' => 'email',
            'audience' => 'parents',
            'recipient_name' => $firstGuardian?->full_name,
            'recipient_contact' => $firstGuardian?->email,
            'recipient_user_id' => $firstGuardian?->user_id,
            'status' => 'sent',
            'created_by_user_id' => $admin->id,
            'sent_at' => now(),
        ]);

        GradingScale::query()->create(['school_id' => $school->id, 'name' => 'Default', 'min_mark' => 80, 'max_mark' => 100, 'grade_letter' => 'A', 'grade_point' => 4.0, 'remarks' => 'Excellent', 'is_default' => true]);
        GradingScale::query()->create(['school_id' => $school->id, 'name' => 'Default', 'min_mark' => 70, 'max_mark' => 79.99, 'grade_letter' => 'B', 'grade_point' => 3.0, 'remarks' => 'Good', 'is_default' => false]);
        GradingScale::query()->create(['school_id' => $school->id, 'name' => 'Default', 'min_mark' => 60, 'max_mark' => 69.99, 'grade_letter' => 'C', 'grade_point' => 2.0, 'remarks' => 'Fair', 'is_default' => false]);
        GradingScale::query()->create(['school_id' => $school->id, 'name' => 'Default', 'min_mark' => 50, 'max_mark' => 59.99, 'grade_letter' => 'D', 'grade_point' => 1.0, 'remarks' => 'Pass', 'is_default' => false]);
        GradingScale::query()->create(['school_id' => $school->id, 'name' => 'Default', 'min_mark' => 0, 'max_mark' => 49.99, 'grade_letter' => 'E', 'grade_point' => 0.0, 'remarks' => 'Fail', 'is_default' => false]);

        $exam = Exam::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'name' => 'Term 1 CAT',
            'exam_type' => 'cat',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->toDateString(),
            'is_published' => true,
        ]);

        $sampleSchedules = Subject::query()->take(3)->get()->map(function ($subject) use ($school, $exam, $courses, $teacherPool) {
            $course = $courses->random();
            $batch = Batch::query()->where('course_id', $course->id)->inRandomOrder()->first();

            return ExamSchedule::query()->create([
                'school_id' => $school->id,
                'exam_id' => $exam->id,
                'course_id' => $course->id,
                'batch_id' => $batch?->id,
                'subject_id' => $subject->id,
                'exam_date' => now()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:30',
                'total_marks' => 100,
                'pass_marks' => 40,
                'invigilator_user_id' => $teacherPool->random()?->id,
            ]);
        });

        foreach ($sampleSchedules as $schedule) {
            $studentsForSchedule = Student::query()
                ->where('course_id', $schedule->course_id)
                ->when($schedule->batch_id, fn ($query) => $query->where('batch_id', $schedule->batch_id))
                ->take(8)
                ->get();

            foreach ($studentsForSchedule as $student) {
                $score = (float) fake()->numberBetween(35, 92);
                $grade = $score >= 80 ? ['A', 4.0] : ($score >= 70 ? ['B', 3.0] : ($score >= 60 ? ['C', 2.0] : ($score >= 50 ? ['D', 1.0] : ['E', 0.0])));

                StudentMark::query()->create([
                    'school_id' => $school->id,
                    'exam_id' => $exam->id,
                    'exam_schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                    'subject_id' => $schedule->subject_id,
                    'marks_obtained' => $score,
                    'grade_letter' => $grade[0],
                    'grade_point' => $grade[1],
                    'entered_by_user_id' => $admin->id,
                ]);
            }
        }

        $tuitionCategory = FeeCategory::query()->create([
            'school_id' => $school->id,
            'name' => 'Tuition',
            'description' => 'Core tuition fees',
            'is_mandatory' => true,
        ]);

        $activityCategory = FeeCategory::query()->create([
            'school_id' => $school->id,
            'name' => 'Activity',
            'description' => 'Sports and club activities',
            'is_mandatory' => false,
        ]);

        $financeStructures = $courses->take(2)->map(function (Course $course) use ($school, $year, $term, $tuitionCategory, $activityCategory): array {
            $batch = Batch::query()->where('course_id', $course->id)->inRandomOrder()->first();

            return [
                FeeStructure::query()->create([
                    'school_id' => $school->id,
                    'academic_year_id' => $year->id,
                    'term_id' => $term->id,
                    'course_id' => $course->id,
                    'batch_id' => $batch?->id,
                    'fee_category_id' => $tuitionCategory->id,
                    'name' => "{$course->name} Term Tuition",
                    'amount' => 35000,
                    'due_date' => now()->addWeeks(2)->toDateString(),
                    'frequency' => 'once',
                    'is_active' => true,
                ]),
                FeeStructure::query()->create([
                    'school_id' => $school->id,
                    'academic_year_id' => $year->id,
                    'term_id' => $term->id,
                    'course_id' => $course->id,
                    'batch_id' => null,
                    'fee_category_id' => $activityCategory->id,
                    'name' => "{$course->name} Activity Fee",
                    'amount' => 5000,
                    'due_date' => now()->addWeeks(3)->toDateString(),
                    'frequency' => 'once',
                    'is_active' => true,
                ]),
            ];
        })->flatten();

        foreach ($financeStructures as $structure) {
            $studentsForStructure = Student::query()
                ->where('course_id', $structure->course_id)
                ->when($structure->batch_id, fn ($query) => $query->where('batch_id', $structure->batch_id))
                ->take(3)
                ->get();

            foreach ($studentsForStructure as $student) {
                $scholarship = (float) fake()->randomElement([0, 0, 1000, 2000]);
                $discount = (float) fake()->randomElement([0, 500, 1000]);
                $fine = 0.0;
                $final = max((float) $structure->amount - $scholarship - $discount + $fine, 0);
                $paid = (float) fake()->randomElement([0, 5000, 10000]);
                $paid = min($paid, $final);
                $balance = max($final - $paid, 0);

                $assignment = FeeAssignment::query()->create([
                    'school_id' => $school->id,
                    'student_id' => $student->id,
                    'fee_structure_id' => $structure->id,
                    'academic_year_id' => $year->id,
                    'term_id' => $term->id,
                    'amount' => $structure->amount,
                    'scholarship_amount' => $scholarship,
                    'discount_amount' => $discount,
                    'fine_amount' => $fine,
                    'final_amount' => $final,
                    'paid_amount' => $paid,
                    'balance_amount' => $balance,
                    'due_date' => $structure->due_date,
                    'status' => $balance <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                    'adjustment_reason' => $scholarship > 0 ? 'Merit scholarship' : null,
                ]);

                if ($paid > 0) {
                    FeePayment::query()->create([
                        'school_id' => $school->id,
                        'student_id' => $student->id,
                        'fee_assignment_id' => $assignment->id,
                        'amount_paid' => $paid,
                        'payment_date' => now()->toDateString(),
                        'payment_method' => fake()->randomElement(['cash', 'bank', 'online']),
                        'transaction_reference' => strtoupper(fake()->bothify('TRX###??')),
                        'receipt_number' => 'SEED-' . strtoupper(fake()->bothify('####??')),
                        'collected_by_user_id' => $admin->id,
                    ]);
                }
            }
        }

        AuditLog::query()->create([
            'school_id' => $school->id,
            'user_id' => $admin->id,
            'action' => 'compliance.report.export.pdf',
            'entity_type' => 'compliance_report',
            'description' => 'Seeded compliance audit log entry',
            'details' => [
                'report_type' => 'enrollment',
                'filters' => [
                    'date_from' => now()->subMonth()->toDateString(),
                    'date_to' => now()->toDateString(),
                ],
                'record_count' => Student::query()->count(),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Seeder',
        ]);
    }
}
