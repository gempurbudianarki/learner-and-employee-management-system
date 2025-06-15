<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LearnerController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\LearnerAttendanceController;
use App\Http\Controllers\AnnouncementController;

/*
|--------------------------------------------------------------------------
| Public / Guest Routes
|--------------------------------------------------------------------------
*/
// Welcome page — only for guests (not logged in)
Route::get('/', function () {
    return view('welcome');
})->middleware('guest');


/*
|--------------------------------------------------------------------------
| Authenticated Redirect Based on Role
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();

    return match (true) {
        $user->hasRole('admin') => redirect('/admin/dashboard'),
        $user->hasRole('employee') => redirect('/employee/dashboard'),
        $user->hasRole('learner') => redirect('/learner/dashboard'),
        default => abort(403),
    };
// })->middleware(['auth'])->name('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated User Routes (All Roles)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
// Route::middleware(['auth'])->group(function () {

    // Show registration form
    Route::get('/register-user', [RegisterController::class, 'showAdminRegisterForm'])->name('admin.register.form');

    // Handle registration and OTP
    Route::post('/register-user', [RegisterController::class, 'registerByAdmin'])->name('admin.register.user');
    Route::get('/verify-otp', [RegisterController::class, 'showOtpForm'])->name('admin.otp.verify.form');
    Route::post('/verify-otp', [RegisterController::class, 'verifyOtp'])->name('admin.otp.verify.submit');

    // Admin
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    // Employee
    Route::get('/employee/dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
    // Learner
    Route::get('/learner/dashboard', [LearnerController::class, 'index'])->name('learner.dashboard');

    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/sendmail', [UserController::class, 'sendMail'])->name('users.sendmail');
    Route::get('/users/sendmail', fn() => redirect()->route('users.index'));

    // Email Logs
    Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email.logs');

    // Custom Email
    Route::get('/custom-email', [UserController::class, 'customEmailForm'])->name('email.custom.form');
    Route::post('/custom-email/send', [UserController::class, 'sendCustomEmail'])->name('email.custom.send');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Edit the User Profile
    Route::get('/admin/profile/edit', [ProfileController::class, 'edit'])
        ->middleware('auth')
        ->name('admin.profile.edit');

    Route::patch('/admin/profile/update', [ProfileController::class, 'update'])
    ->middleware('auth')
    ->name('admin.profile.update');

    // Update password from admin profile page
    Route::put('/admin/profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('auth')
        ->name('admin.profile.password');

    // Delete account from admin profile page
    Route::delete('/admin/profile/delete', [ProfileController::class, 'destroy'])
        ->middleware('auth')
        ->name('admin.profile.destroy');

    Route::get('attendance', [LearnerAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::post('attendance/store', [LearnerAttendanceController::class, 'store'])->name('admin.attendance.store');
    Route::post('attendance/lookup-learner', [LearnerAttendanceController::class, 'lookupLearner'])
    ->middleware(['auth', 'verified'])
    ->name('admin.attendance.lookup-learner');
});

Route::middleware(['auth', 'verified'])
    ->prefix('admin/announcements')
    ->name('admin.announcements.')
    ->group(function () {

       // List & create announcements
    Route::get('/', [AnnouncementController::class, 'index'])->name('index');
    Route::post('/', [AnnouncementController::class, 'store'])->name('store');

    // Show send form
    Route::get('/send', [AnnouncementController::class, 'sendForm'])->name('sendForm');

    //  Process sending to selected recipients
    Route::post('/send', [AnnouncementController::class, 'processSend'])->name('processSend');

    // Send a specific announcement by ID (e.g. quick resend)
    Route::get('/{id}/send', [AnnouncementController::class, 'send'])->name('send');

    // View logs
    Route::get('/logs', [AnnouncementController::class, 'logs'])->name('logs');
});


// Temporarily allow public access for testing purposes~
Route::resource('learners', LearnerController::class)->names('admin.learners');
    // Route::resource('employees', EmployeeController::class);
    // Route::resource('attendance', AttendanceController::class);
    // Route::resource('announcements', AnnouncementController::class);
Route::delete('/learners/{id}', [LearnerController::class, 'destroy'])->name('learners.destroy');




// // Admin-only routes for managing records
// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::resource('learners', LearnerController::class)->names('admin.learners');
//     // Add more admin-only routes here later
// });


/*
|--------------------------------------------------------------------------
| Auth Routes (Login, Register, Password, etc.)
|--------------------------------------------------------------------------
*/

// Breeze auth routes (login, register, etc.)
require __DIR__.'/auth.php';
