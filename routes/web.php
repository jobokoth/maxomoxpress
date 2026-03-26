<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DailyOperationsController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ComplianceReportController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\Admin\PlatformSettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MpesaWebhookController;
use App\Http\Controllers\PlatformBillingController;
use App\Http\Controllers\QuickBooksController;
use App\Http\Controllers\Onboarding\SchoolSetupWizardController;
use App\Http\Controllers\Onboarding\SchoolRegistrationController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\Portal\ParentPortalController as ParentPortal;
use App\Http\Controllers\Portal\StudentPortalController as StudentPortal;
use App\Http\Controllers\StudentServicesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ─── Landing page ─────────────────────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ─── PayStack Webhooks (public, no auth, no CSRF) ─────────────────────────────
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('paystack')
    ->name('paystack.')
    ->group(function (): void {
        Route::post('/webhook', [PlatformBillingController::class, 'webhook'])->name('webhook');
    });

// ─── Platform Billing (school-initiated payment) ──────────────────────────────
Route::middleware(['auth'])
    ->prefix('billing')
    ->name('platform.billing.')
    ->group(function (): void {
        Route::get('/{school_slug}/initiate', [PlatformBillingController::class, 'initiate'])->name('initiate');
        Route::get('/{school_slug}/callback', [PlatformBillingController::class, 'callback'])->name('callback');
    });

// ─── Mpesa Webhooks (public, no auth, no CSRF) ────────────────────────────────
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('mpesa')
    ->name('mpesa.')
    ->group(function (): void {
        Route::post('/validation/{school_slug}', [MpesaWebhookController::class, 'validation'])->name('validation');
        Route::post('/confirmation/{school_slug}', [MpesaWebhookController::class, 'confirmation'])->name('confirmation');
        Route::post('/stk-callback', [MpesaWebhookController::class, 'stkCallback'])->name('stk.callback');
    });

// School self-registration (public)
Route::get('/register/school', [SchoolRegistrationController::class, 'show'])->name('school.register');
Route::post('/register/school', [SchoolRegistrationController::class, 'store'])->name('school.register.store');

// Onboarding wizard (auth required)
Route::middleware(['auth', 'not.onboarded'])->group(function (): void {
    Route::get('/onboarding', [SchoolSetupWizardController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/step', [SchoolSetupWizardController::class, 'step'])->name('onboarding.step');
});

// Platform Settings — super admin only (no Filament panel yet)
Route::prefix('admin/platform-settings')
    ->middleware(['auth'])
    ->name('admin.platform-settings.')
    ->group(function (): void {
        Route::get('/', [PlatformSettingController::class, 'index'])->name('index');
        Route::get('/create', [PlatformSettingController::class, 'create'])->name('create');
        Route::post('/', [PlatformSettingController::class, 'store'])->name('store');
        Route::get('/{platformSetting}/edit', [PlatformSettingController::class, 'edit'])->name('edit');
        Route::put('/{platformSetting}', [PlatformSettingController::class, 'update'])->name('update');
        Route::delete('/{platformSetting}', [PlatformSettingController::class, 'destroy'])->name('destroy');
    });

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ─── Parent Portal ────────────────────────────────────────────────────────────
Route::prefix('s/{school_slug}/portal/parent')
    ->middleware(['auth', 'tenant.school', 'parent.portal'])
    ->name('portal.parent.')
    ->group(function (): void {
        Route::get('/', [ParentPortal::class, 'dashboard'])->name('dashboard');
        Route::get('/fees/{student}', [ParentPortal::class, 'fees'])->name('fees');
        Route::get('/attendance/{student}', [ParentPortal::class, 'attendance'])->name('attendance');
        Route::get('/results/{student}', [ParentPortal::class, 'results'])->name('results');
        Route::get('/announcements', [ParentPortal::class, 'announcements'])->name('announcements');
        Route::get('/events', [ParentPortal::class, 'events'])->name('events');
    });

// ─── Student Portal ───────────────────────────────────────────────────────────
Route::prefix('s/{school_slug}/portal/student')
    ->middleware(['auth', 'tenant.school', 'student.portal'])
    ->name('portal.student.')
    ->group(function (): void {
        Route::get('/', [StudentPortal::class, 'dashboard'])->name('dashboard');
        Route::get('/timetable', [StudentPortal::class, 'timetable'])->name('timetable');
        Route::get('/results', [StudentPortal::class, 'results'])->name('results');
        Route::get('/attendance', [StudentPortal::class, 'attendance'])->name('attendance');
        Route::get('/library', [StudentPortal::class, 'library'])->name('library');
    });

// ─── School admin / staff routes ──────────────────────────────────────────────
Route::prefix('s/{school_slug}')
    ->middleware(['auth', 'tenant.school', 'school.access'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

        Route::middleware('permission:academic-years.manage')->group(function (): void {
            Route::get('/academic-years', [AcademicController::class, 'academicYears'])->name('tenant.academic-years.index');
            Route::post('/academic-years', [AcademicController::class, 'storeAcademicYear'])->name('tenant.academic-years.store');
            Route::get('/academic-years/{academicYear}/edit', [AcademicController::class, 'editAcademicYear'])->whereNumber('academicYear')->name('tenant.academic-years.edit');
            Route::put('/academic-years/{academicYear}', [AcademicController::class, 'updateAcademicYear'])->whereNumber('academicYear')->name('tenant.academic-years.update');
        });

        Route::middleware('permission:terms.manage')->group(function (): void {
            Route::get('/terms', [AcademicController::class, 'terms'])->name('tenant.terms.index');
            Route::post('/terms', [AcademicController::class, 'storeTerm'])->name('tenant.terms.store');
            Route::get('/terms/{term}/edit', [AcademicController::class, 'editTerm'])->whereNumber('term')->name('tenant.terms.edit');
            Route::put('/terms/{term}', [AcademicController::class, 'updateTerm'])->whereNumber('term')->name('tenant.terms.update');
        });

        Route::middleware('permission:departments.manage')->group(function (): void {
            Route::get('/departments', [AcademicController::class, 'departments'])->name('tenant.departments.index');
            Route::post('/departments', [AcademicController::class, 'storeDepartment'])->name('tenant.departments.store');
            Route::get('/departments/{department}/edit', [AcademicController::class, 'editDepartment'])->whereNumber('department')->name('tenant.departments.edit');
            Route::put('/departments/{department}', [AcademicController::class, 'updateDepartment'])->whereNumber('department')->name('tenant.departments.update');
        });

        Route::middleware('permission:courses.manage')->group(function (): void {
            Route::get('/courses', [AcademicController::class, 'courses'])->name('tenant.courses.index');
            Route::post('/courses', [AcademicController::class, 'storeCourse'])->name('tenant.courses.store');
            Route::get('/courses/{course}/edit', [AcademicController::class, 'editCourse'])->whereNumber('course')->name('tenant.courses.edit');
            Route::put('/courses/{course}', [AcademicController::class, 'updateCourse'])->whereNumber('course')->name('tenant.courses.update');
        });

        Route::middleware('permission:batches.manage')->group(function (): void {
            Route::get('/batches', [AcademicController::class, 'batches'])->name('tenant.batches.index');
            Route::post('/batches', [AcademicController::class, 'storeBatch'])->name('tenant.batches.store');
            Route::get('/batches/{batch}/edit', [AcademicController::class, 'editBatch'])->whereNumber('batch')->name('tenant.batches.edit');
            Route::put('/batches/{batch}', [AcademicController::class, 'updateBatch'])->whereNumber('batch')->name('tenant.batches.update');
        });

        Route::middleware('permission:subjects.view')->group(function (): void {
            Route::get('/subjects', [AcademicController::class, 'subjects'])->name('tenant.subjects.index');
        });
        Route::middleware('permission:subjects.manage')->group(function (): void {
            Route::post('/subjects', [AcademicController::class, 'storeSubject'])->name('tenant.subjects.store');
            Route::get('/subjects/{subject}/edit', [AcademicController::class, 'editSubject'])->whereNumber('subject')->name('tenant.subjects.edit');
            Route::put('/subjects/{subject}', [AcademicController::class, 'updateSubject'])->whereNumber('subject')->name('tenant.subjects.update');
        });
        Route::middleware('permission:teacher-assignments.manage')->group(function (): void {
            Route::post('/subject-assignments', [AcademicController::class, 'assignSubjectTeacher'])->name('tenant.subject-assignments.store');
            Route::delete('/subject-assignments/{assignment}', [AcademicController::class, 'destroyAssignment'])->whereNumber('assignment')->name('tenant.subject-assignments.destroy');
        });

        Route::middleware('permission:attendance.manage')->group(function (): void {
            Route::get('/operations/attendance', [DailyOperationsController::class, 'attendance'])->name('tenant.operations.attendance.index');
            Route::post('/operations/attendance/students', [DailyOperationsController::class, 'storeStudentAttendance'])->name('tenant.operations.attendance.students.store');
            Route::post('/operations/attendance/staff', [DailyOperationsController::class, 'storeStaffAttendance'])->name('tenant.operations.attendance.staff.store');
        });

        Route::middleware('permission:timetable.manage')->group(function (): void {
            Route::get('/operations/timetable', [DailyOperationsController::class, 'timetable'])->name('tenant.operations.timetable.index');
            Route::post('/operations/timetable', [DailyOperationsController::class, 'storeTimetableEntry'])->name('tenant.operations.timetable.store');
            Route::put('/operations/timetable/{entry}', [DailyOperationsController::class, 'updateTimetableEntry'])->whereNumber('entry')->name('tenant.operations.timetable.update');
            Route::delete('/operations/timetable/{entry}', [DailyOperationsController::class, 'destroyTimetableEntry'])->whereNumber('entry')->name('tenant.operations.timetable.destroy');
        });

        Route::middleware('permission:announcements.view')->group(function (): void {
            Route::get('/operations/announcements', [DailyOperationsController::class, 'announcements'])->name('tenant.operations.announcements.index');
            Route::post('/operations/announcements/{announcement}/read', [DailyOperationsController::class, 'markAnnouncementRead'])->whereNumber('announcement')->name('tenant.operations.announcements.read');
        });
        Route::middleware('permission:announcements.manage')->group(function (): void {
            Route::post('/operations/announcements', [DailyOperationsController::class, 'storeAnnouncement'])->name('tenant.operations.announcements.store');
            Route::put('/operations/announcements/{announcement}', [DailyOperationsController::class, 'updateAnnouncement'])->whereNumber('announcement')->name('tenant.operations.announcements.update');
            Route::delete('/operations/announcements/{announcement}', [DailyOperationsController::class, 'destroyAnnouncement'])->whereNumber('announcement')->name('tenant.operations.announcements.destroy');
        });

        Route::middleware('permission:assessments.manage')->group(function (): void {
            Route::get('/assessment/exams', [AssessmentController::class, 'exams'])->name('tenant.assessment.exams.index');
            Route::post('/assessment/exams', [AssessmentController::class, 'storeExam'])->name('tenant.assessment.exams.store');
            Route::put('/assessment/exams/{exam}', [AssessmentController::class, 'updateExam'])->whereNumber('exam')->name('tenant.assessment.exams.update');
            Route::post('/assessment/schedules', [AssessmentController::class, 'storeSchedule'])->name('tenant.assessment.schedules.store');
            Route::put('/assessment/schedules/{schedule}', [AssessmentController::class, 'updateSchedule'])->whereNumber('schedule')->name('tenant.assessment.schedules.update');
        });

        Route::middleware('permission:marks.manage')->group(function (): void {
            Route::get('/assessment/marks', [AssessmentController::class, 'marks'])->name('tenant.assessment.marks.index');
            Route::post('/assessment/marks', [AssessmentController::class, 'storeMarks'])->name('tenant.assessment.marks.store');
            Route::post('/assessment/grading-rules', [AssessmentController::class, 'storeGradingRule'])->name('tenant.assessment.grading-rules.store');
            Route::put('/assessment/grading-rules/{gradingRule}', [AssessmentController::class, 'updateGradingRule'])->whereNumber('gradingRule')->name('tenant.assessment.grading-rules.update');
            Route::delete('/assessment/grading-rules/{gradingRule}', [AssessmentController::class, 'destroyGradingRule'])->whereNumber('gradingRule')->name('tenant.assessment.grading-rules.destroy');
        });

        Route::middleware('permission:reports.view')->group(function (): void {
            Route::get('/assessment/reports', [AssessmentController::class, 'reports'])->name('tenant.assessment.reports.index');
            Route::get('/compliance/reports', [ComplianceReportController::class, 'index'])->name('tenant.compliance.reports.index');
            Route::get('/compliance/reports/{report}/pdf', [ComplianceReportController::class, 'exportPdf'])->whereIn('report', ['enrollment', 'attendance', 'performance', 'fees'])->name('tenant.compliance.reports.export.pdf');
            Route::get('/compliance/reports/{report}/excel', [ComplianceReportController::class, 'exportExcel'])->whereIn('report', ['enrollment', 'attendance', 'performance', 'fees'])->name('tenant.compliance.reports.export.excel');
        });

        // Payment settings (school admin only)
        Route::middleware('permission:fees.manage')->group(function (): void {
            Route::get('/settings/payments', [PaymentSettingsController::class, 'show'])->name('tenant.settings.payments');
            Route::post('/settings/payments', [PaymentSettingsController::class, 'update'])->name('tenant.settings.payments.update');
            Route::post('/settings/payments/register-urls', [PaymentSettingsController::class, 'registerUrls'])->name('tenant.settings.payments.register-urls');

            // ── QuickBooks Integration ─────────────────────────────────────────────
            Route::get('/settings/quickbooks', [QuickBooksController::class, 'settings'])->name('tenant.quickbooks.settings');
            Route::get('/settings/quickbooks/connect', [QuickBooksController::class, 'connect'])->name('tenant.quickbooks.connect');
            Route::get('/settings/quickbooks/callback', [QuickBooksController::class, 'callback'])->name('tenant.quickbooks.callback');
            Route::post('/settings/quickbooks/disconnect', [QuickBooksController::class, 'disconnect'])->name('tenant.quickbooks.disconnect');
            Route::post('/settings/quickbooks/sync-all', [QuickBooksController::class, 'syncAll'])->name('tenant.quickbooks.sync-all');
        });

        Route::middleware('permission:fees.manage')->group(function (): void {
            Route::get('/finance', [FinanceController::class, 'index'])->name('tenant.finance.index');
            Route::post('/finance/categories', [FinanceController::class, 'storeCategory'])->name('tenant.finance.categories.store');
            Route::post('/finance/structures', [FinanceController::class, 'storeStructure'])->name('tenant.finance.structures.store');
            Route::post('/finance/invoices/generate', [FinanceController::class, 'generateInvoices'])->name('tenant.finance.invoices.generate');
            Route::put('/finance/assignments/{assignment}/adjustments', [FinanceController::class, 'updateAssignmentAdjustments'])->whereNumber('assignment')->name('tenant.finance.assignments.adjustments.update');
            Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('tenant.finance.payments.store');
            Route::get('/finance/receipts/{payment}', [FinanceController::class, 'receipt'])->whereNumber('payment')->name('tenant.finance.receipts.show');
            Route::get('/finance/receipts/{payment}/pdf', [FinanceController::class, 'receiptPdf'])->whereNumber('payment')->name('tenant.finance.receipts.pdf');
            Route::get('/finance/statements/{student}', [FinanceController::class, 'statement'])->whereNumber('student')->name('tenant.finance.statements.show');
            Route::get('/finance/statements/{student}/pdf', [FinanceController::class, 'statementPdf'])->whereNumber('student')->name('tenant.finance.statements.pdf');
        });

        Route::middleware('permission:student-services.manage')->group(function (): void {
            Route::get('/services', [StudentServicesController::class, 'index'])->name('tenant.services.index');
            Route::post('/services/discipline', [StudentServicesController::class, 'storeIncident'])->name('tenant.services.discipline.store');
            Route::post('/services/clinic', [StudentServicesController::class, 'storeClinicRecord'])->name('tenant.services.clinic.store');
            Route::post('/services/library/books', [StudentServicesController::class, 'storeBook'])->name('tenant.services.library.books.store');
            Route::post('/services/library/issues', [StudentServicesController::class, 'issueBook'])->name('tenant.services.library.issues.store');
            Route::post('/services/library/issues/{issue}/return', [StudentServicesController::class, 'returnBook'])->whereNumber('issue')->name('tenant.services.library.issues.return');
            Route::post('/services/transport/routes', [StudentServicesController::class, 'storeTransportRoute'])->name('tenant.services.transport.routes.store');
            Route::post('/services/transport/vehicles', [StudentServicesController::class, 'storeVehicle'])->name('tenant.services.transport.vehicles.store');
            Route::post('/services/transport/assignments', [StudentServicesController::class, 'assignTransport'])->name('tenant.services.transport.assignments.store');
            Route::post('/services/hostels', [StudentServicesController::class, 'storeHostel'])->name('tenant.services.hostels.store');
            Route::post('/services/hostel-rooms', [StudentServicesController::class, 'storeHostelRoom'])->name('tenant.services.hostel-rooms.store');
            Route::post('/services/hostel-allocations', [StudentServicesController::class, 'allocateHostel'])->name('tenant.services.hostel-allocations.store');
            Route::post('/services/hostel-allocations/{allocation}/vacate', [StudentServicesController::class, 'vacateHostelAllocation'])->whereNumber('allocation')->name('tenant.services.hostel-allocations.vacate');
        });

        Route::middleware('permission:communications.manage')->group(function (): void {
            Route::get('/communication', [CommunicationController::class, 'index'])->name('tenant.communication.index');
            Route::post('/communication/parents/access', [CommunicationController::class, 'grantParentPortalAccess'])->name('tenant.communication.parents.access');
            Route::post('/communication/notifications', [CommunicationController::class, 'sendNotification'])->name('tenant.communication.notifications.send');
            Route::post('/communication/events', [CommunicationController::class, 'storeEvent'])->name('tenant.communication.events.store');
            Route::post('/communication/events/{event}/reminders', [CommunicationController::class, 'sendEventReminder'])->whereNumber('event')->name('tenant.communication.events.reminders.send');
            Route::get('/communication/parents/{guardian}/portal', [CommunicationController::class, 'parentPortal'])->whereNumber('guardian')->name('tenant.communication.parents.portal');
        });

        Route::middleware('permission:parent-portal.view')->group(function (): void {
            Route::get('/parent-portal', [ParentPortalController::class, 'index'])->name('tenant.parent-portal.index');
        });

        Route::middleware('permission:students.view')->group(function (): void {
            Route::get('/students', [StudentController::class, 'index'])->name('tenant.students.index');
            Route::get('/students/alumni', [StudentController::class, 'alumni'])->name('tenant.students.alumni');
            Route::get('/students/{student}', [StudentController::class, 'show'])
                ->whereNumber('student')
                ->name('tenant.students.show');
        });

        Route::middleware('permission:students.manage')->group(function (): void {
            Route::get('/students/create', [StudentController::class, 'create'])->name('tenant.students.create');
            Route::post('/students', [StudentController::class, 'store'])->name('tenant.students.store');
            Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->whereNumber('student')->name('tenant.students.edit');
            Route::put('/students/{student}', [StudentController::class, 'update'])->whereNumber('student')->name('tenant.students.update');
            Route::post('/students/{student}/transition', [StudentController::class, 'transition'])->whereNumber('student')->name('tenant.students.transition');
            Route::post('/students/{student}/guardians', [StudentController::class, 'addGuardian'])->whereNumber('student')->name('tenant.students.guardians.store');
            Route::post('/students/{student}/lifecycle/promote', [StudentController::class, 'promote'])->whereNumber('student')->name('tenant.students.lifecycle.promote');
            Route::post('/students/{student}/lifecycle/repeat', [StudentController::class, 'repeat'])->whereNumber('student')->name('tenant.students.lifecycle.repeat');
            Route::post('/students/{student}/lifecycle/transfer', [StudentController::class, 'transfer'])->whereNumber('student')->name('tenant.students.lifecycle.transfer');
            Route::post('/students/{student}/lifecycle/graduate', [StudentController::class, 'graduate'])->whereNumber('student')->name('tenant.students.lifecycle.graduate');
            Route::post('/students/{student}/lifecycle/exit/initiate', [StudentController::class, 'initiateExit'])->whereNumber('student')->name('tenant.students.lifecycle.exit.initiate');
            Route::post('/students/{student}/lifecycle/clearances/{clearance}', [StudentController::class, 'updateClearance'])->whereNumber(['student', 'clearance'])->name('tenant.students.lifecycle.clearances.update');
            Route::post('/students/{student}/lifecycle/exit/complete', [StudentController::class, 'completeExit'])->whereNumber('student')->name('tenant.students.lifecycle.exit.complete');
        });
    });
