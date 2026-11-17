<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::get('/recuperar-clave', [ForgotPasswordController::class, 'create'])->name('password.request');
Route::post('/recuperar-clave', [ForgotPasswordController::class, 'store'])
    ->middleware('throttle:password-recovery')
    ->name('password.email');
Route::get('/restablecer-clave/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
Route::post('/restablecer-clave', [ResetPasswordController::class, 'store'])
    ->middleware('throttle:password-reset')
    ->name('password.update');
Route::get('/panel', PanelController::class)
    ->middleware(['auth', 'auth.session', 'can:panel.acceder'])
    ->name('panel');
Route::get('/panel/usuarios', [UserController::class, 'index'])
    ->middleware(['auth', 'auth.session', 'can:panel.acceder'])
    ->name('users.index');
Route::get('/panel/usuarios/crear', [UserController::class, 'create'])
    ->middleware(['auth', 'auth.session', 'can:panel.acceder'])
    ->name('users.create');
Route::post('/panel/usuarios', [UserController::class, 'store'])
    ->middleware(['auth', 'auth.session', 'can:panel.acceder'])
    ->name('users.store');
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');
