<?php

use Illuminate\Support\Facades\Route;

$frontendUrl = static fn (string $path) => redirect()->away(rtrim((string) config('app.frontend_url'), '/').'/'.ltrim($path, '/'));

Route::get('/login', fn () => $frontendUrl('/login'))->name('login');
Route::get('/register', fn () => $frontendUrl('/register'))->name('register');
Route::get('/forgot-password', fn () => $frontendUrl('/login?forgot=1'))->name('password.request');
Route::get('/reset-password/{token}', fn () => $frontendUrl('/login?forgot=1'))->name('password.reset');
