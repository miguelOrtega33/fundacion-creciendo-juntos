<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::get('/recuperar-clave', [ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/recuperar-clave', [ForgotPasswordController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.email');
Route::get('/restablecer-clave/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('/restablecer-clave', [ResetPasswordController::class, 'store'])->name('password.update');
Route::get('/panel', PanelController::class)->middleware(['auth', 'auth.session'])->name('panel');
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');
