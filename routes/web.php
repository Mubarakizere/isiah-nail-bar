<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Service;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\Category;
use App\Models\ServiceTag;

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\ProviderReviewController;
use App\Http\Controllers\ProviderServiceController;
use App\Http\Controllers\ProviderCalendarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\ProviderWorkingHourController;
use App\Http\Controllers\HomeController;
use App\Services\SmsService;
use App\Http\Controllers\Webhook\WeFlexfyWebhookController;
use Illuminate\Support\Facades\Log;
use App\Services\TwilioService;
use App\Jobs\SendBookingReminder;
use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\AdminSlotController;

  Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
// Default dashboard redirect by role
Route::get('/dashboard', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') return redirect()->route('dashboard.admin');
        if (Auth::user()->role === 'provider') return redirect()->route('dashboard.provider');
        if (Auth::user()->role === 'customer') return redirect()->route('dashboard.customer');
    }
    return redirect('/');
})->middleware('auth')->name('dashboard');

// Public routes
Route::get('/', function () {
    $services = Service::latest()->take(6)->get();
    return view('home.index', compact('services'));
});
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/faq', 'faq')->name('faq');
Route::view('/about', 'about')->name('about');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::post('/webhook/weflexfy', [\App\Http\Controllers\PaymentWebhookController::class, 'handleWeFlexfyWebhook'])
    ->middleware('throttle:60,1') // ✅ SECURITY: Rate limit webhooks to 60 per minute
    ->withoutMiddleware([
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        HandlePrecognitiveRequests::class
    ])
    ->name('weflexfy.webhook');

Route::get('/services', function (Request $request) {
    $query = Service::query();

    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category); // ✅ FIXED
    }

    if ($request->filled('tag')) {
        $query->whereHas('tags', fn($q) => $q->where('tag', $request->tag));
    }

    $services = $query->latest()->paginate(9);

    $categories = Category::whereIn('id', Service::distinct()->pluck('category_id'))->get();
    $tags = ServiceTag::select('tag')->distinct()->pluck('tag')->filter();

    return view('services.index', compact('services', 'categories', 'tags'));
});

Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

Route::get('/providers', fn() => view('providers.index', [
    'providers' => Provider::where('active', true)->get(),
    'teamMembers' => \App\Models\TeamMember::where('active', true)->orderBy('display_order')->get()
]));


Route::view('/contact', 'contact.index');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Booking steps
Route::controller(BookingController::class)->group(function () {
    Route::get('/booking', 'step1')->name('booking.step1');
    Route::post('/booking/step1', 'postStep1')->name('booking.step1.submit');
    Route::get('/booking/step2', 'step2')->name('booking.step2');
    Route::post('/booking/step2', 'postStep2')->name('booking.step2.submit');
    Route::get('/booking/step3', 'step3')->name('booking.step3');
    Route::post('/booking/step3', 'postStep3')->name('booking.step3.submit');
    Route::middleware('auth')->group(function () {
    Route::get('/booking/step4', [BookingController::class, 'step4'])->name('booking.step4');
    Route::post('/booking/step4', [BookingController::class, 'postStep4'])->name('booking.step4.submit');
    Route::get('/booking/step5', [BookingController::class, 'step5'])->name('booking.step5');
    Route::post('/booking/step5', [BookingController::class, 'postStep5'])->name('booking.step5.submit');
      });
    Route::view('/booking/success', 'booking.success')->name('booking.success');
    Route::get('/booking/{id}/receipt', 'receipt')->name('booking.receipt');
    Route::post('/booking/pay', [PaymentController::class, 'initiate'])
        ->middleware('throttle:10,1') // ✅ SECURITY: Limit payment initiations to 10 per minute
        ->name('booking.pay');
    Route::get('/booking/payment-iframe', [BookingController::class, 'showPaymentIframe'])->name('booking.paymentIframe');
    Route::get('/booking/payment-status/{reference}', [BookingController::class, 'checkPaymentStatus'])->name('booking.checkPaymentStatus');
    Route::post('/booking/mark-failed/{reference}', [BookingController::class, 'markPaymentFailed'])->name('booking.markFailed');

});


    // ✅ Shared route for both customer and admin to download PDF
    Route::get('/booking/{id}/download-receipt', [BookingController::class, 'downloadReceipt'])->name('download.receipt');

// Authenticated user routes
Route::middleware(['auth', 'verified'])->group(function () {
Route::get('/booking/retry-payment/{id}', [BookingController::class, 'retryPayment'])
    ->middleware(['auth', 'verified', 'role:customer'])
    ->name('booking.retryPayment');

Route::post('/booking/retry-payment/{id}', [BookingController::class, 'retryPaymentPost'])
    ->middleware(['auth', 'verified', 'role:customer'])
    ->name('booking.retryPayment.post');


    // Customer Dashboard
    Route::prefix('dashboard/customer')->middleware('role:customer')->group(function () {
        Route::get('/', function (Request $request) {
            $status = $request->query('filter', 'upcoming');
            $query = Booking::with(['service', 'provider'])->where('customer_id', Auth::user()->customer_id);

            $status === 'upcoming'
                ? $query->whereDate('date', '>=', now())
                : $query->whereDate('date', '<', now());

            $bookings = $query->latest()->paginate(10);
            $notifications = Auth::user()->unreadNotifications ?? [];

            return view('dashboard.customer.index', compact('bookings', 'status', 'notifications'));
        })->name('dashboard.customer');

        Route::get('/booking/{id}', fn($id) => view('dashboard.customer.show', [
            'booking' => Booking::where('id', $id)->where('customer_id', Auth::user()->customer_id)->with(['service', 'provider'])->firstOrFail()
        ]))->name('customer.booking.show');

        Route::patch('/booking/{id}/cancel', fn($id) => tap(
            Booking::where('id', $id)->where('customer_id', Auth::user()->customer_id)->where('status', 'pending')->firstOrFail(),
            fn($booking) => $booking->update(['status' => 'cancelled'])
        ))->name('customer.booking.cancel');
          Route::get('/notifications', function () {
    $notifications = Auth::user()->notifications()->latest()->paginate(10);
    return view('dashboard.customer.notifications', compact('notifications'));
})->name('customer.notifications');

        Route::get('/booking/pay-remaining/{id}', [BookingController::class, 'payRemaining'])->name('customer.booking.payRemaining');
Route::post('/booking/pay-remaining/{id}', [BookingController::class, 'payRemainingPost'])->name('customer.booking.payRemainingPost');

        Route::post('/notifications/{id}/mark', fn($id) => tap(Auth::user()->notifications()->find($id), fn($n) => $n?->markAsRead()))->name('notifications.mark');
        Route::get('/reviews', [CustomerReviewController::class, 'index'])->name('customer.reviews.index');
        Route::delete('/reviews/{id}', [CustomerReviewController::class, 'destroy'])->name('customer.reviews.destroy');
    });
Route::get('/dashboard/reviews/create', [ReviewController::class, 'create'])->name('customer.reviews.create');
Route::post('/dashboard/reviews/store', [ReviewController::class, 'store'])->name('customer.reviews.store');

    // ✅ Provider Dashboard (working inline logic)
    Route::prefix('dashboard/provider')->middleware('role:provider')->group(function () {
        Route::get('/', function () {
    $provider = Auth::user()->provider;

    $myBookings = Booking::where('provider_id', $provider->id)
    ->with(['services', 'customer.user', 'payments'])
    ->latest()
    ->get();


    $completed = $myBookings->where('status', 'completed')->count();
    $total = $myBookings->count();
    $rate = $total ? round(($completed / $total) * 100, 1) : 0;

    // ✅ FIXED: Revenue via booking_service pivot
    $revenue = DB::table('bookings')
        ->join('booking_service', 'bookings.id', '=', 'booking_service.booking_id')
        ->join('services', 'booking_service.service_id', '=', 'services.id')
        ->where('bookings.provider_id', $provider->id)
        ->where('bookings.status', 'completed')
        ->sum('services.price');

    // ✅ FIXED: Revenue chart via pivot
    $start = Carbon::now()->subDays(6)->startOfDay();
    $end = Carbon::now()->endOfDay();

    $rawData = DB::table('bookings')
        ->join('booking_service', 'bookings.id', '=', 'booking_service.booking_id')
        ->join('services', 'booking_service.service_id', '=', 'services.id')
        ->where('bookings.provider_id', $provider->id)
        ->where('bookings.status', 'completed')
        ->whereBetween('bookings.date', [$start, $end])
        ->select(DB::raw('DATE(bookings.date) as date'), DB::raw('SUM(services.price) as total'))
        ->groupBy('date')
        ->pluck('total', 'date');

    $chartData = collect();
    for ($i = 6; $i >= 0; $i--) {
        $date = Carbon::now()->subDays($i)->toDateString();
        $chartData->push([
            'label' => Carbon::parse($date)->format('M d'),
            'total' => (float)($rawData[$date] ?? 0)
        ]);
    }

    return view('dashboard.provider', compact(
        'myBookings',
        'completed',
        'total',
        'rate',
        'revenue',
        'chartData'
    ));
})->name('dashboard.provider');

Route::get('/calendar/sync', [ProviderCalendarController::class, 'exportICal'])
    ->name('provider.calendar.ical');
        Route::patch('/booking/{id}/complete', function ($id) {
    $booking = Booking::where('id', $id)
        ->where('provider_id', Auth::user()->provider->id)
        ->where('status', 'accepted')
        ->firstOrFail();

    $booking->update(['status' => 'completed']);

    return back()->with('status', 'Booking marked as completed.');
})->name('provider.booking.complete');


            Route::put('/dashboard/admin/booking/{id}', function ($id) {
    $booking = Booking::findOrFail($id);
    $status = request('status');

    if (in_array($status, ['pending', 'accepted', 'declined', 'completed', 'cancelled'])) {
        $booking->status = $status;
        $booking->save();
    }

    return back()->with('status', 'Booking status updated successfully.');
})->name('dashboard.admin.update');
            Route::post('/booking/{id}/{action}', function ($id, $action) {
    $booking = \App\Models\Booking::where('id', $id)
        ->where('provider_id', auth()->user()->provider->id)
        ->firstOrFail();

    $map = [
        'accept' => 'accepted',
        'decline' => 'declined'
    ];

    if (array_key_exists($action, $map)) {
        $booking->status = $map[$action];
        $booking->save();
    }

    return back()->with('status', 'Booking status updated.');
})->name('provider.booking.action');

               Route::post('/notifications/mark-all', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return back()->with('status', 'All notifications marked as read.');
})->name('notifications.markAll');

        Route::get('/services', fn() => view('dashboard.provider.services.index', [
            'services' => Auth::user()->provider->services()->paginate(10)
        ]))->name('provider.services.index');
        Route::get('/notifications', function () {
            $notifications = Auth::user()->notifications()->latest()->paginate(10);
            return view('dashboard.provider.notifications', compact('notifications'));
        })->name('provider.notifications');

        Route::post('/notifications/{id}/mark', function ($id) {
            Auth::user()->notifications()->findOrFail($id)?->markAsRead();
            return back();
        })->name('notifications.mark');
        Route::get('/dashboard/provider/earnings/report', [\App\Http\Controllers\ProviderEarningsController::class, 'downloadReport'])
        ->middleware('role:provider')
        ->name('provider.earnings.pdf');
        Route::get('/services/{service}/edit', [ProviderServiceController::class, 'edit'])->name('provider.services.edit');
        Route::post('/services/{service}/update', [ProviderServiceController::class, 'update'])->name('provider.services.update');
        Route::put('/services/{service}', [ProviderServiceController::class, 'update'])->name('provider.services.update');
        Route::delete('/services/{service}', [ProviderServiceController::class, 'destroy'])->name('provider.services.destroy');
        Route::get('/services/request', [ProviderServiceController::class, 'create'])->name('provider.services.request');
        Route::post('/services/request', [ProviderServiceController::class, 'store'])->name('provider.services.store');
        Route::get('/reviews', [ProviderReviewController::class, 'index'])->name('provider.reviews.index');
        Route::get('/calendar', [\App\Http\Controllers\ProviderCalendarController::class, 'index'])->name('provider.calendar');
        Route::get('/calendar', [ProviderCalendarController::class, 'index'])->name('provider.calendar');
        Route::get('/calendar/fetch', [ProviderCalendarController::class, 'fetch'])->name('provider.calendar.fetch');
        Route::get('/working-hours', [\App\Http\Controllers\ProviderWorkingHourController::class, 'edit'])->name('provider.working-hours.edit');
        Route::post('/working-hours', [\App\Http\Controllers\ProviderWorkingHourController::class, 'update'])->name('provider.working-hours.update');
    });




    // Admin Dashboard
    Route::prefix('dashboard/admin')->middleware('role:admin')->group(function () {
        Route::get('/gallery-instagram', [\App\Http\Controllers\Admin\GalleryInstagramController::class, 'index'])->name('admin.gallery-instagram.index');
    Route::post('/gallery-instagram', [\App\Http\Controllers\Admin\GalleryInstagramController::class, 'store'])->name('admin.gallery-instagram.store');
    Route::delete('/gallery-instagram/{galleryInstagram}', [\App\Http\Controllers\Admin\GalleryInstagramController::class, 'destroy'])->name('admin.gallery-instagram.destroy');
        Route::get('/', [AdminBookingController::class, 'adminDashboard'])->name('dashboard.admin');
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('dashboard.admin.bookings');
        Route::put('/dashboard/admin/booking/{id}', [AdminBookingController::class, 'update'])
    ->name('dashboard.admin.update');


        Route::get('/messages', fn() => view('dashboard.admin.messages', [
            'messages' => \App\Models\Contact::latest()->paginate(10)
        ]))->name('admin.messages');

        Route::get('/reports', [AdminBookingController::class, 'revenueReport'])->name('dashboard.admin.reports');
        Route::get('/reports/pdf', [AdminBookingController::class, 'revenueReportPdf'])->name('dashboard.admin.reports.pdf');

          // All Providers Performance
Route::get('/dashboard/admin/providers/performance', [ProviderController::class, 'allPerformance'])->name('admin.providers.performance');

// Single Provider Performance
Route::get('/dashboard/admin/providers/{provider}/performance', [ProviderController::class, 'performance'])->name('admin.providers.performance.single');
Route::delete('/admin/providers/{provider}', [ProviderController::class, 'destroy'])->name('admin.providers.destroy');
 

   Route::get('/slots', [AdminSlotController::class, 'index'])->name('admin.slots.index');
Route::post('/slots/block', [AdminSlotController::class, 'block'])->name('admin.slots.block');
Route::post('/slots/unblock', [AdminSlotController::class, 'unblock'])->name('admin.slots.unblock');

        Route::prefix('admin')->group(function () {
            Route::resource('services', AdminServiceController::class)->names('admin.services');
            Route::resource('providers', ProviderController::class)->names('admin.providers');
            Route::patch('/providers/{provider}/approve', [ProviderController::class, 'approve'])->name('admin.providers.approve');
            Route::patch('/providers/{provider}/decline', [ProviderController::class, 'decline'])->name('admin.providers.decline');
            Route::get('/providers/{provider}/working-hours', [ProviderWorkingHourController::class, 'edit'])->name('admin.providers.hours.edit');
            Route::post('/providers/{provider}/working-hours', [ProviderWorkingHourController::class, 'update'])->name('admin.providers.hours.update');
        });
        Route::get('/admin/webhooks', [\App\Http\Controllers\Admin\WebhookLogController::class, 'index'])->name('admin.webhooks.index');
        
        // Manual Booking
        Route::get('/bookings/manual/create', [\App\Http\Controllers\Admin\ManualBookingController::class, 'create'])->name('admin.bookings.manual.create');
        Route::post('/bookings/manual', [\App\Http\Controllers\Admin\ManualBookingController::class, 'store'])->name('admin.bookings.manual.store');
        
        // Email Logs
        Route::get('/emails', [\App\Http\Controllers\Admin\EmailLogController::class, 'index'])->name('admin.emails.index');
        Route::get('/emails/{emailLog}', [\App\Http\Controllers\Admin\EmailLogController::class, 'show'])->name('admin.emails.show');
        
        Route::get('/pending-services', [\App\Http\Controllers\Admin\PendingServiceController::class, 'index'])->name('admin.services.pending');
        Route::post('/pending-services/{service}/approve', [\App\Http\Controllers\Admin\PendingServiceController::class, 'approve'])->name('admin.services.approve');
        Route::post('/pending-services/{service}/reject', [\App\Http\Controllers\Admin\PendingServiceController::class, 'reject'])->name('admin.services.reject');
        Route::get('/bookings/{id}/receipt', [\App\Http\Controllers\Admin\BookingController::class, 'receipt'])->name('admin.booking.receipt');
        Route::resource('/categories', \App\Http\Controllers\Admin\CategoryController::class)->names('admin.categories');
        Route::resource('team-members', \App\Http\Controllers\Admin\TeamMemberController::class)->names('admin.team_members');
        Route::resource('/tags', \App\Http\Controllers\TagController::class)->except(['show'])->names('admin.tags');

        Route::get('/reviews', [ReviewController::class, 'adminIndex'])->name('admin.reviews.index');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');

        // Webmail
        Route::get('/webmail', [\App\Http\Controllers\Admin\WebmailController::class, 'index'])->name('admin.webmail.index');
        Route::get('/webmail/{uid}', [\App\Http\Controllers\Admin\WebmailController::class, 'show'])->name('admin.webmail.show');
        Route::post('/webmail/{uid}/reply', [\App\Http\Controllers\Admin\WebmailController::class, 'reply'])->name('admin.webmail.reply');
        Route::delete('/webmail/{uid}', [\App\Http\Controllers\Admin\WebmailController::class, 'destroy'])->name('admin.webmail.delete');
        
        // Notification API (Polling)
        Route::get('/api/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'unread'])->name('admin.notifications.unread');
        Route::post('/api/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('admin.notifications.read');
        Route::get('/notifications/mark-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('admin.notifications.markAllRead');
    });
        Route::get('/dashboard/admin/notifications', function () {
    $notifications = Auth::user()->notifications()->latest()->paginate(10);
    return view('dashboard.admin.notifications', compact('notifications'));
})->middleware(['auth', 'role:admin'])->name('admin.notifications');

Route::post('/dashboard/admin/notifications/{id}/mark', function ($id) {
    Auth::user()->notifications()->find($id)?->markAsRead();
    return back();
})->name('admin.notifications.mark');

Route::post('/dashboard/admin/notifications/mark-all', function () {
    Auth::user()->unreadNotifications->markAsRead();
    return back()->with('status', 'All marked as read.');
})->name('admin.notifications.markAll');

Route::delete('/messages/{id}', function ($id) {
    \App\Models\Contact::findOrFail($id)->delete();
    return back()->with('success', 'Message deleted successfully.');
})->name('admin.messages.destroy');



    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth
require __DIR__ . '/auth.php';
