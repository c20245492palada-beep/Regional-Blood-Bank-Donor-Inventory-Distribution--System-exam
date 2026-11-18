<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
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
    Route::post('/settings/phone/send-code', [AccountSettingsController::class, 'sendPhoneVerificationCode'])
        ->middleware('throttle:3,1')
        ->name('settings.phone.send-code');
    Route::post('/settings/phone/verify', [AccountSettingsController::class, 'verifyPhoneNumber'])
        ->middleware('throttle:10,1')
        ->name('settings.phone.verify');
    Route::put('/settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
