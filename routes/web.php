<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\MasterActivityController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

    // Schedules & Gantt API Routes
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::post('/schedules/{schedule}/reschedule', [ScheduleController::class, 'reschedule'])->name('schedules.reschedule');
    Route::post('/schedules/{schedule}/continue-same-day', [ScheduleController::class, 'continueSameDay'])->name('schedules.continue');

    // Progress Monitoring API Routes
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');
    Route::post('/progress', [ProgressController::class, 'store'])->name('progress.store');

    // Audit Trail & Research Dataset Export Routes
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit-logs/export-csv', [AuditLogController::class, 'exportCsv'])->name('audit.export');

    // Contractor Companies Directory Routes
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    // Projects Directory Routes
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Master Construction Activity Catalog Routes
    Route::get('/master-activities', [MasterActivityController::class, 'index'])->name('master-activities.index');
    Route::post('/master-activities', [MasterActivityController::class, 'store'])->name('master-activities.store');
    Route::put('/master-activities/{masterActivity}', [MasterActivityController::class, 'update'])->name('master-activities.update');
    Route::delete('/master-activities/{masterActivity}', [MasterActivityController::class, 'destroy'])->name('master-activities.destroy');

    // User & Role Management Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
