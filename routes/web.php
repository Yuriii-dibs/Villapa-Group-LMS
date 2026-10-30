<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CourseApprovalController as AdminCourseApprovalController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SectionController as AdminSectionController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\AcademicTermController as AdminAcademicTermController;
use App\Http\Controllers\Admin\OfferingController as AdminOfferingController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ChatModerationController as AdminChatModerationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\Instructor\ClassSessionController as InstructorClassSessionController;
use App\Http\Controllers\Instructor\CourseController as InstructorCourseController;
use App\Http\Controllers\Instructor\DashboardController as InstructorDashboardController;
use App\Http\Controllers\Instructor\LessonController as InstructorLessonController;
use App\Http\Controllers\Instructor\OfferingController as InstructorOfferingController;
use App\Http\Controllers\Instructor\AssignmentController as InstructorAssignmentController;
use App\Http\Controllers\Instructor\AnnouncementController as InstructorAnnouncementController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ClassesController as StudentClassesController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\EnrollmentController as StudentEnrollmentController;
use App\Http\Controllers\Student\LearningController as StudentLearningController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [CatalogController::class, 'home'])->name('home');

// Public Course Catalog
Route::get('/courses', [CatalogController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CatalogController::class, 'show'])->name('courses.show');

// Public Program Catalog
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{course:slug}', [ProgramController::class, 'show'])->name('programs.show');

// Authentication Routes (Accessible directly so users can switch accounts or sign in)
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Pending approval notice (accessible to guests after registration)
Route::get('/register/pending', fn () => view('auth.register-pending'))->name('register.pending');

// Authenticated Account & Profile Routes
Route::middleware(['auth', 'not.suspended'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Course Enrollment
    Route::post('/courses/{course:slug}/enroll', [StudentEnrollmentController::class, 'store'])->name('courses.enroll');

    // Student Classroom & Lessons
    Route::get('/student/courses/{course:slug}', [StudentLearningController::class, 'showCourse'])->name('student.courses.show');
    Route::get('/student/courses/{course:slug}/lessons/{lesson:slug}', [StudentLearningController::class, 'showLesson'])->name('student.lessons.show');
    Route::post('/student/lessons/{lesson}/complete', [StudentLearningController::class, 'completeLesson'])->name('student.lessons.complete');

    // Chat Routes (Throttled posting at 30 messages/minute)
    Route::get('/messages', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/messages/offerings/{offering}', [ChatController::class, 'offeringRoom'])->name('chat.offering');
    Route::post('/messages/offerings/{offering}', [ChatController::class, 'sendOfferingMessage'])->middleware('throttle:30,1')->name('chat.offering.send');
    Route::get('/messages/courses/{course}', [ChatController::class, 'courseRoom'])->name('chat.course');
    Route::post('/messages/courses/{course}', [ChatController::class, 'sendCourseMessage'])->middleware('throttle:30,1')->name('chat.course.send');
    Route::get('/messages/direct/{recipient}', [ChatController::class, 'directRoom'])->name('chat.direct');
    Route::post('/messages/direct/{recipient}', [ChatController::class, 'sendDirectMessage'])->middleware('throttle:30,1')->name('chat.direct.send');

    // Schedule
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

    // Academic Calendar Routes
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');

    // Online Class Session Joining & Attendance
    Route::get('/classes/{session}', [ClassSessionController::class, 'show'])->name('classes.show');
});

// Student Portal Routes
Route::middleware(['auth', 'not.suspended', 'role:' . Role::STUDENT])
    ->prefix('student')
    ->as('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        // Online Classes & Enrolled Subjects
        Route::get('/classes', [StudentClassesController::class, 'index'])->name('classes.index');
        Route::get('/classes/{subject}', [StudentClassesController::class, 'show'])->name('classes.show');
        Route::post('/classes/assignments/{assignment}/submit', [StudentClassesController::class, 'submitAssignment'])->name('classes.assignments.submit');

        // Assignments List & Details
        Route::get('/assignments', [StudentClassesController::class, 'assignments'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentClassesController::class, 'showAssignment'])->name('assignments.show');
    });

// Instructor Portal Routes
Route::middleware(['auth', 'not.suspended', 'role:' . Role::INSTRUCTOR])
    ->prefix('instructor')
    ->as('instructor.')
    ->group(function () {
        Route::get('/dashboard', [InstructorDashboardController::class, 'index'])->name('dashboard');

        // Class Offerings (My Classes) & Meet link
        Route::get('/offerings', [InstructorOfferingController::class, 'index'])->name('offerings.index');
        Route::get('/offerings/{offering}', [InstructorOfferingController::class, 'show'])->name('offerings.show');
        Route::post('/offerings/{offering}/meet-link', [InstructorOfferingController::class, 'updateMeetLink'])->name('offerings.update-meet-link');

        // Coursework & Assignments Management
        Route::resource('assignments', InstructorAssignmentController::class);
        Route::get('/assignments/{assignment}/submissions', [InstructorAssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/submissions/{submission}/grade', [InstructorAssignmentController::class, 'grade'])->name('assignments.grade');

        // Post Announcements
        Route::post('/announcements', [InstructorAnnouncementController::class, 'store'])->name('announcements.store');

        // Course Management
        Route::get('/courses', [InstructorCourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [InstructorCourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [InstructorCourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [InstructorCourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [InstructorCourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [InstructorCourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/submit-for-approval', [InstructorCourseController::class, 'submitForApproval'])->name('courses.submit');

        // Students Enrolled in Courses
        Route::get('/students', [InstructorCourseController::class, 'students'])->name('students.index');

        // Lesson Management
        Route::get('/courses/{course}/lessons/create', [InstructorLessonController::class, 'create'])->name('lessons.create');
        Route::post('/courses/{course}/lessons', [InstructorLessonController::class, 'store'])->name('lessons.store');
        Route::get('/lessons/{lesson}/edit', [InstructorLessonController::class, 'edit'])->name('lessons.edit');
        Route::put('/lessons/{lesson}', [InstructorLessonController::class, 'update'])->name('lessons.update');
        Route::delete('/lessons/{lesson}', [InstructorLessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('/lessons/{lesson}/move-up', [InstructorLessonController::class, 'moveUp'])->name('lessons.move-up');
        Route::post('/lessons/{lesson}/move-down', [InstructorLessonController::class, 'moveDown'])->name('lessons.move-down');

        // Online Class Sessions Management
        Route::get('/classes', [InstructorClassSessionController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [InstructorClassSessionController::class, 'create'])->name('classes.create');
        Route::post('/classes', [InstructorClassSessionController::class, 'store'])->name('classes.store');
        Route::get('/classes/{session}/edit', [InstructorClassSessionController::class, 'edit'])->name('classes.edit');
        Route::put('/classes/{session}', [InstructorClassSessionController::class, 'update'])->name('classes.update');
        Route::delete('/classes/{session}', [InstructorClassSessionController::class, 'destroy'])->name('classes.destroy');
        Route::post('/classes/{session}/start', [InstructorClassSessionController::class, 'start'])->name('classes.start');
        Route::post('/classes/{session}/end', [InstructorClassSessionController::class, 'end'])->name('classes.end');
        Route::get('/classes/{session}/attendance', [InstructorClassSessionController::class, 'attendance'])->name('classes.attendance');
    });

// Admin Console Routes
Route::middleware(['auth', 'not.suspended', 'role:' . Role::ADMIN])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Category Management
        Route::resource('categories', AdminCategoryController::class)->except(['show']);

        // Course Approval & Listing Workflow
        Route::get('/courses', [AdminCourseApprovalController::class, 'allCourses'])->name('courses.index');
        Route::get('/courses/pending', [AdminCourseApprovalController::class, 'index'])->name('courses.pending');
        Route::post('/courses/{course}/approve', [AdminCourseApprovalController::class, 'approve'])->name('courses.approve');
        Route::post('/courses/{course}/reject', [AdminCourseApprovalController::class, 'reject'])->name('courses.reject');
        Route::post('/courses/{course}/unpublish', [AdminCourseApprovalController::class, 'unpublish'])->name('courses.unpublish');

        // User Administration, Approvals & Password Reset
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('/users/store-student', [AdminUserController::class, 'storeStudent'])->name('users.store-student');
        Route::post('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.update-status');
        Route::post('/users/{user}/program', [AdminUserController::class, 'updateProgram'])->name('users.update-program');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/approve-instructor', [AdminUserController::class, 'approveInstructor'])->name('users.approve-instructor');
        Route::post('/users/{user}/approve-student', [AdminUserController::class, 'approveStudent'])->name('users.approve-student');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // Year Levels, Sections & Section Subject Scheduling
        Route::get('/sections/{section}/schedule', [AdminSectionController::class, 'schedule'])->name('sections.schedule');
        Route::post('/sections/{section}/schedule', [AdminSectionController::class, 'storeSchedule'])->name('sections.schedule.store');
        Route::resource('sections', AdminSectionController::class)->except(['show', 'create', 'edit']);

        // Subjects Catalog Management
        Route::resource('subjects', AdminSubjectController::class)->except(['show', 'create', 'edit']);

        // Academic Terms Management
        Route::resource('terms', AdminAcademicTermController::class)->except(['show', 'create', 'edit']);
        Route::post('/terms/{term}/set-current', [AdminAcademicTermController::class, 'setCurrent'])->name('terms.set-current');

        // Course Offerings Management & Schedules
        Route::resource('offerings', AdminOfferingController::class);

        // Enrollment Hub ("Enroll Whole Section" & Irregular Student Picker with conflict warning)
        Route::get('/enrollment', [AdminEnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('/enrollment/enroll-section', [AdminEnrollmentController::class, 'enrollSection'])->name('enrollment.enroll-section');
        Route::post('/enrollment/enroll-student', [AdminEnrollmentController::class, 'enrollStudent'])->name('enrollment.enroll-student');

        // Announcements Management (Global, Year Level, Offering)
        Route::resource('announcements', AdminAnnouncementController::class)->only(['index', 'store', 'destroy']);

        // Chat Moderation
        Route::get('/chat-moderation', [AdminChatModerationController::class, 'index'])->name('chat-moderation.index');
        Route::delete('/chat-moderation/{message}', [AdminChatModerationController::class, 'destroy'])->name('chat-moderation.destroy');
    });

// Super Admin Center Routes
Route::middleware(['auth', 'not.suspended', 'role:' . Role::SUPER_ADMIN])
    ->prefix('super-admin')
    ->as('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    });
