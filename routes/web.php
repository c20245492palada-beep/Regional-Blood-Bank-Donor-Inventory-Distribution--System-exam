<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BloodBagController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistributionLogController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\HospitalRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [AccountSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [AccountSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/donors', [DonorController::class, 'store'])->name('donors.store');
    Route::post('/blood-bags', [BloodBagController::class, 'storeFromDashboard'])->name('blood-bags.store');
    Route::post('/hospital-requests', [HospitalRequestController::class, 'store'])->name('hospital-requests.store');
    Route::post('/distribution-logs', [DistributionLogController::class, 'storeFromDashboard'])->name('distribution-logs.store');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
