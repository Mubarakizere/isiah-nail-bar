<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$frontendRedirect = static function (string $path = '') {
    $base = rtrim((string) config('app.frontend_url', config('app.url')), '/');
    $target = $base.'/'.ltrim($path, '/');
    return redirect()->away($target);
};

// Laravel serves API and operational endpoints; public pages belong to Next.js.
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::post('/webhook/weflexfy', [PaymentWebhookController::class, 'handleWeFlexfyWebhook'])
    ->middleware('throttle:60,1')
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        HandlePrecognitiveRequests::class,
    ])
    ->name('weflexfy.webhook');

Route::get('/', fn () => $frontendRedirect('/'))->name('home');
Route::get('/dashboard', fn () => $frontendRedirect('/dashboard'))->name('dashboard');
Route::get('/dashboard/provider', fn () => $frontendRedirect('/dashboard/provider'))->name('provider.dashboard');
Route::get('/booking', fn () => $frontendRedirect('/booking'))->name('booking.step1');
Route::get('/booking/step2', fn () => $frontendRedirect('/booking/step2'))->name('booking.step2');
Route::get('/booking/step3', fn () => $frontendRedirect('/booking/step3'))->name('booking.step3');
Route::get('/booking/step4', fn () => $frontendRedirect('/booking/step4'))->name('booking.step4');
Route::get('/booking/step5', fn () => $frontendRedirect('/booking/step5'))->name('booking.step5');
Route::get('/booking/success', fn () => $frontendRedirect('/booking/success'))->name('booking.success');
Route::get('/booking/payment-iframe', fn (Request $request) => $frontendRedirect('/booking/payment?'.http_build_query($request->query())))
    ->name('booking.paymentIframe');
Route::get('/booking/{id}/receipt', fn ($id) => $frontendRedirect('/dashboard?booking='.urlencode((string) $id)))
    ->name('booking.receipt');

// Payment provider callbacks remain on Laravel so signed/provider redirects still complete.
Route::get('/payment/callback', [PaymentController::class, 'flutterwaveCallback'])->name('flutterwave.callback');

Route::fallback(function (Request $request) use ($frontendRedirect) {
    if (!$request->isMethodSafe()) {
        return response()->json(['message' => 'This endpoint is not available.'], 404);
    }

    $path = trim($request->path(), '/');
    $aliases = [
        'dashboard/admin' => 'admin',
        'dashboard/admin/bookings' => 'admin/bookings',
        'dashboard/admin/reports' => 'admin/reports',
        'dashboard/customer' => 'dashboard',
        'dashboard/provider' => 'dashboard/provider',
    ];
    $path = $aliases[$path] ?? $path;
    $response = $frontendRedirect($path);

    if ($request->getQueryString()) {
        $response->setTargetUrl($response->getTargetUrl().'?'.$request->getQueryString());
    }

    return $response;
});

require __DIR__.'/auth.php';
