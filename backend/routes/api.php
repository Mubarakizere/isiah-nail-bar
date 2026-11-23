<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PublicApiController;
use App\Http\Controllers\Api\V1\AdminServiceApiController;
use App\Http\Controllers\Api\V1\AdminProviderApiController;
use App\Http\Controllers\Api\V1\AdminTeamMemberApiController;
use App\Http\Controllers\Api\V1\AdminMessageApiController;
use App\Http\Controllers\Api\V1\AdminHeroSlideApiController;
use App\Http\Controllers\Api\V1\AdminPickupLocationApiController;
use App\Http\Controllers\Api\V1\AdminGalleryInstagramApiController;
use App\Http\Controllers\Api\V1\AdminTagApiController;
use App\Http\Controllers\Api\V1\AdminReviewApiController;
use App\Http\Controllers\Api\V1\AdminManualBookingApiController;
use App\Http\Controllers\Api\V1\AdminWalkInApiController;
use App\Http\Controllers\Api\V1\AdminCalendarApiController;
use App\Http\Controllers\Api\V1\BookingCheckoutApiController;
use App\Http\Controllers\Api\V1\AdminPendingServiceApiController;
use App\Http\Controllers\Api\V1\AdminEmailLogApiController;
use App\Http\Controllers\Api\V1\AdminWebhookLogApiController;
use App\Http\Controllers\Api\V1\AdminSettingApiController;
use App\Http\Controllers\Admin\WebmailController;
use App\Http\Controllers\Admin\AdminSlotController;
use App\Http\Controllers\Api\V1\AdminRecallApiController;
use App\Http\Controllers\Api\V1\CustomerLoyaltyApiController;
use App\Http\Controllers\Api\V1\AdminLoyaltyApiController;
use App\Http\Controllers\PaymentWebhookController;

// This API alias uses the same signed handler as /webhook/weflexfy.
Route::post('/webhook/weflexfy', [PaymentWebhookController::class, 'handleWeFlexfyWebhook'])
    ->middleware('throttle:60,1');

// V1 API Endpoints for Headless Next.js Frontend
Route::prefix('v1')->group(function () {
    // Public Endpoints
    Route::get('/home', [PublicApiController::class, 'home']);
    Route::get('/services', [PublicApiController::class, 'services']);
    Route::get('/services/{service}', [PublicApiController::class, 'service']);
    Route::get('/categories', [PublicApiController::class, 'categories']);
    Route::get('/providers', [PublicApiController::class, 'providers']);
    Route::get('/reviews', [PublicApiController::class, 'reviews']);
    Route::get('/gallery', [PublicApiController::class, 'gallery']);
    Route::get('/settings', [PublicApiController::class, 'settings']);
    Route::post('/contact', [PublicApiController::class, 'submitContact']);
    Route::get('/booking/options', [BookingCheckoutApiController::class, 'options']);
    Route::get('/booking/availability', [BookingCheckoutApiController::class, 'availability'])->middleware('throttle:60,1');
    Route::post('/booking/checkout', [BookingCheckoutApiController::class, 'checkout'])->middleware('throttle:10,1');
    Route::post('/booking/retry/{reference}', [BookingCheckoutApiController::class, 'retry'])->middleware('throttle:5,1');
    Route::get('/booking/status/{reference}', [BookingCheckoutApiController::class, 'status'])->middleware('throttle:60,1');
    Route::post('/auth/login', [PublicApiController::class, 'login']);
    Route::post('/auth/register', [PublicApiController::class, 'register']);
    Route::post('/auth/logout', [PublicApiController::class, 'logout']);
    Route::get('/auth/me', [PublicApiController::class, 'currentUser']);

    // Customer data is always derived from the authenticated user, never a query-string email.
    Route::middleware('api.auth:customer')->prefix('customer')->group(function () {
        Route::get('/bookings', [PublicApiController::class, 'customerBookings']);
        Route::post('/bookings/{id}/cancel', [PublicApiController::class, 'cancelCustomerBooking']);
        Route::get('/bookings/{id}/receipt-pdf', [PublicApiController::class, 'downloadCustomerReceiptPdf']);
        Route::put('/profile', [PublicApiController::class, 'updateCustomerProfile']);
        Route::post('/bookings/{id}/pay-remaining', [BookingCheckoutApiController::class, 'payRemaining'])->middleware('throttle:5,1');
        Route::get('/loyalty', [CustomerLoyaltyApiController::class, 'index']);
    });

    Route::middleware('api.auth:provider')->prefix('provider')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\Api\V1\ProviderApiController::class, 'overview']);
        Route::post('/bookings/{id}/status', [\App\Http\Controllers\Api\V1\ProviderApiController::class, 'updateBookingStatus']);
    });

    // Admin Dashboard Endpoints
    Route::middleware('api.auth:admin')->group(function () {
    Route::get('/admin/overview', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'overview']);
    Route::get('/admin/bookings', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'bookings']);
    Route::get('/admin/calendar', [AdminCalendarApiController::class, 'index']);
    Route::get('/admin/bookings/{id}', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'bookingDetail']);
    Route::post('/admin/bookings/{id}/status', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'updateBookingStatus']);
    Route::post('/admin/bookings/{id}/record-payment', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'recordPayment']);
    Route::put('/admin/bookings/{id}', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'updateBooking']);
    Route::get('/admin/bookings/{id}/receipt-pdf', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'downloadReceiptPdf']);
    Route::get('/admin/reports', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'reports']);
    Route::get('/admin/reports/pdf', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'reportsPdf']);
    Route::get('/admin/performance', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'performance']);
    Route::get('/admin/categories', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'categories']);
    Route::post('/admin/categories', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'storeCategory']);
    Route::put('/admin/categories/{id}', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'updateCategory']);
    Route::delete('/admin/categories/{id}', [\App\Http\Controllers\Api\V1\AdminApiController::class, 'deleteCategory']);

    // Keep service APIs consistent with the existing Next.js admin APIs.
    Route::prefix('admin/services')->group(function () {
        Route::get('/', [AdminServiceApiController::class, 'index']);
        Route::post('/', [AdminServiceApiController::class, 'store']);
        Route::put('/{service}', [AdminServiceApiController::class, 'update']);
        Route::delete('/{service}', [AdminServiceApiController::class, 'destroy']);
        Route::post('/{service}/approve', [AdminServiceApiController::class, 'approve']);
        Route::post('/{service}/reject', [AdminServiceApiController::class, 'reject']);
    });

    Route::get('/admin/pending-services', [AdminPendingServiceApiController::class, 'index']);
    Route::post('/admin/pending-services/{service}/approve', [AdminPendingServiceApiController::class, 'approve']);
    Route::post('/admin/pending-services/{service}/reject', [AdminPendingServiceApiController::class, 'reject']);

    Route::prefix('admin/providers')->group(function () {
        Route::get('/', [AdminProviderApiController::class, 'index']);
        Route::post('/', [AdminProviderApiController::class, 'store']);
        Route::get('/{provider}/working-hours', [AdminProviderApiController::class, 'workingHours']);
        Route::put('/{provider}/working-hours', [AdminProviderApiController::class, 'updateWorkingHours']);
        Route::put('/{provider}', [AdminProviderApiController::class, 'update']);
        Route::delete('/{provider}', [AdminProviderApiController::class, 'destroy']);
        Route::post('/{provider}/approve', [AdminProviderApiController::class, 'approve']);
        Route::post('/{provider}/decline', [AdminProviderApiController::class, 'decline']);
    });

    Route::prefix('admin/team-members')->group(function () {
        Route::get('/', [AdminTeamMemberApiController::class, 'index']);
        Route::post('/', [AdminTeamMemberApiController::class, 'store']);
        Route::put('/{teamMember}', [AdminTeamMemberApiController::class, 'update']);
        Route::delete('/{teamMember}', [AdminTeamMemberApiController::class, 'destroy']);
    });

    Route::get('/admin/messages', [AdminMessageApiController::class, 'index']);
    Route::post('/admin/messages/{contact}/read', [AdminMessageApiController::class, 'markRead']);
    Route::delete('/admin/messages/{contact}', [AdminMessageApiController::class, 'destroy']);

    Route::get('/admin/hero-slides', [AdminHeroSlideApiController::class, 'index']);
    Route::post('/admin/hero-slides', [AdminHeroSlideApiController::class, 'store']);
    Route::post('/admin/hero-slides/{heroSlide}', [AdminHeroSlideApiController::class, 'update']);
    Route::delete('/admin/hero-slides/{heroSlide}', [AdminHeroSlideApiController::class, 'destroy']);
    Route::post('/admin/hero-slides/{heroSlide}/toggle', [AdminHeroSlideApiController::class, 'toggle']);

    Route::get('/admin/pickup-locations', [AdminPickupLocationApiController::class, 'index']);
    Route::post('/admin/pickup-locations', [AdminPickupLocationApiController::class, 'store']);
    Route::put('/admin/pickup-locations/{pickupLocation}', [AdminPickupLocationApiController::class, 'update']);
    Route::post('/admin/pickup-locations/{pickupLocation}/toggle', [AdminPickupLocationApiController::class, 'toggle']);
    Route::delete('/admin/pickup-locations/{pickupLocation}', [AdminPickupLocationApiController::class, 'destroy']);

    Route::get('/admin/gallery', [AdminGalleryInstagramApiController::class, 'index']);
    Route::post('/admin/gallery', [AdminGalleryInstagramApiController::class, 'store']);
    Route::delete('/admin/gallery/{galleryInstagram}', [AdminGalleryInstagramApiController::class, 'destroy']);

    Route::get('/admin/tags', [AdminTagApiController::class, 'index']);
    Route::post('/admin/tags', [AdminTagApiController::class, 'store']);
    Route::put('/admin/tags/{tag}', [AdminTagApiController::class, 'update']);
    Route::delete('/admin/tags/{tag}', [AdminTagApiController::class, 'destroy']);

    Route::get('/admin/reviews', [AdminReviewApiController::class, 'index']);
    Route::post('/admin/reviews', [AdminReviewApiController::class, 'store']);
    Route::put('/admin/reviews/{review}', [AdminReviewApiController::class, 'update']);
    Route::delete('/admin/reviews/{review}', [AdminReviewApiController::class, 'destroy']);

    Route::get('/admin/bookings/manual/options', [AdminManualBookingApiController::class, 'options']);
    Route::post('/admin/bookings/manual', [AdminManualBookingApiController::class, 'store']);
    Route::get('/admin/bookings/walk-in/options', [AdminWalkInApiController::class, 'options']);
    Route::post('/admin/bookings/walk-in', [AdminWalkInApiController::class, 'store']);

    Route::get('/admin/email-logs', [AdminEmailLogApiController::class, 'index']);
    Route::get('/admin/email-logs/{emailLog}', [AdminEmailLogApiController::class, 'show']);

    Route::get('/admin/webhooks', [AdminWebhookLogApiController::class, 'index']);

    Route::get('/admin/settings', [AdminSettingApiController::class, 'index']);
    Route::put('/admin/settings', [AdminSettingApiController::class, 'update']);

    Route::get('/admin/webmail', [WebmailController::class, 'index']);
    Route::get('/admin/webmail/{uid}', [WebmailController::class, 'show']);
    Route::get('/admin/webmail/{uid}/attachments/{attachmentId}', [WebmailController::class, 'downloadAttachment']);
    Route::post('/admin/webmail/{uid}/reply', [WebmailController::class, 'reply']);
    Route::delete('/admin/webmail/{uid}', [WebmailController::class, 'destroy']);

    Route::get('/admin/slots', [AdminSlotController::class, 'index']);
    Route::post('/admin/slots/block', [AdminSlotController::class, 'block']);
    Route::post('/admin/slots/unblock', [AdminSlotController::class, 'unblock']);

    Route::get('/admin/recalls', [AdminRecallApiController::class, 'index']);
    Route::post('/admin/recalls/{customerId}/log-contact', [AdminRecallApiController::class, 'logContact']);
    Route::post('/admin/recalls/{customerId}/send-email', [AdminRecallApiController::class, 'sendEmail']);

    // Loyalty and Stamp Cards
    Route::get('/admin/loyalty/cards', [AdminLoyaltyApiController::class, 'cards']);
    Route::post('/admin/loyalty/cards/{customerId}/adjust', [AdminLoyaltyApiController::class, 'adjust']);
    Route::get('/admin/loyalty/rewards', [AdminLoyaltyApiController::class, 'rewards']);
    Route::post('/admin/loyalty/rewards/{id}', [AdminLoyaltyApiController::class, 'updateReward']);
    Route::post('/admin/loyalty/vouchers/{code}/redeem', [AdminLoyaltyApiController::class, 'redeemVoucher']);
    });
});
