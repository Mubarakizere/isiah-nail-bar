<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Review;
use App\Models\HeroSlide;
use App\Models\Setting;
use App\Models\GalleryInstagram;
use App\Models\Contact;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Models\Customer;
use App\Support\ApiToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Support\PdfTemplates;

class PublicApiController extends Controller
{
    /**
     * Homepage bootstrap data (services, hero slides, reviews, stats)
     */
    public function home()
    {
        $featuredServices = Service::with('category')
            ->where(function($q) {
                $q->whereNull('approved')->orWhere('approved', true);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        $latestServices = Service::with('category')
            ->where(function($q) {
                $q->whereNull('approved')->orWhere('approved', true);
            })
            ->latest()
            ->take(6)
            ->get();

        $reviews = Review::latest()->take(10)->get();
        $reviewCount = Review::count();
        $averageRating = $reviewCount > 0 ? round(Review::avg('rating'), 1) : 4.9;

        $heroSlides = HeroSlide::active()->ordered()->get()->map(fn (HeroSlide $slide) => [
            'id' => $slide->id,
            'title' => $slide->title,
            'subtitle' => $slide->subtitle,
            'description' => $slide->description,
            // Keep the URL relative so the Next.js /storage rewrite serves the Laravel upload.
            'image' => '/storage/' . ltrim($slide->image, '/'),
            'button_text' => $slide->button_text,
            'button_url' => $slide->button_url,
            'secondary_button_text' => $slide->secondary_button_text,
            'secondary_button_url' => $slide->secondary_button_url,
            'is_active' => (bool) $slide->is_active,
            'sort_order' => (int) $slide->sort_order,
            'created_at' => $slide->created_at?->toISOString(),
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'featured_services' => $featuredServices,
                'latest_services' => $latestServices,
                'reviews' => $reviews,
                'review_count' => $reviewCount,
                'average_rating' => $averageRating,
                'hero_slides' => $heroSlides,
            ]
        ]);
    }

    /**
     * List all services with filtering by category, search, and price
     */
    public function services(Request $request)
    {
        $query = Service::with(['category', 'provider', 'tags'])
            ->where(function($q) {
                $q->whereNull('approved')->orWhere('approved', true);
            });

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $services = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Single service detail
     */
    public function service(Service $service)
    {
        $service->load(['category', 'provider', 'tags', 'reviews']);

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    /**
     * All service categories
     */
    public function categories()
    {
        $categories = Category::withCount('services')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Active providers / nail artists
     */
    public function providers()
    {
        $providers = Provider::with(['workingHours', 'services'])
            ->where('active', true)
            ->get();

        $teamMembers = class_exists(\App\Models\TeamMember::class)
            ? \App\Models\TeamMember::where('active', true)->orderBy('display_order')->get()
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'providers' => $providers,
                'team_members' => $teamMembers,
            ],
        ]);
    }

    /**
     * Reviews
     */
    public function reviews(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $reviews = Review::latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Salon public settings & business info
     */
    public function settings()
    {
        $defaultInfo = [
            'salon_name' => 'Isaiah Nail Bar',
            'phone' => '+250 790 395 169',
            'phone_raw' => '0790395169',
            'whatsapp' => '+250790395169',
            'email' => 'info@isaiahnailbar.com',
            'address' => 'KG 4 Roundabout, Gisementi, Kigali, Rwanda',
            'location_landmark' => 'Opposite Sonatubes direction, with dedicated client parking',
            'opening_hours' => 'Monday – Saturday: 08:00 AM – 08:00 PM',
            'opening_hours_sunday' => 'Sunday: Closed (Private bookings upon request)',
            'google_map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15950.019185121285!2d30.108678!3d-1.954736!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x19dca6ee46244f67%3A0x6291244078657662!2sKisimenti!5e0!3m2!1sen!2srw!4v1683838383838!5m2!1sen!2srw',
            'google_map_link' => 'https://maps.google.com/?q=Isaiah+Nail+Bar+Gisementi+Kigali',
            'currency' => 'RWF',
            'instagram' => 'https://www.instagram.com/isaiah_nails_art_kigali_rwanda',
            'facebook' => 'https://www.facebook.com/isaiahnailbar',
            'google_review_url' => 'https://g.page/r/CS4QpNuz_MJkEAE/review',
        ];
        // Only known public business details may leave this endpoint. Settings
        // such as booking_alert_emails and integration credentials are private.
        $dbSettings = Setting::query()
            ->whereIn('key', array_keys($defaultInfo))
            ->whereNotNull('value')
            ->pluck('value', 'key')
            ->filter(fn ($v) => trim((string) $v) !== '')
            ->all();

        return response()->json([
            'success' => true,
            'data' => array_merge($defaultInfo, $dbSettings),
        ]);
    }

    /**
     * Instagram Gallery Posts
     */
    public function gallery()
    {
        $posts = GalleryInstagram::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Submit Contact Message
     */
    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|min:3',
        ]);

        $messageContent = $validated['message'];
        if (!empty($validated['phone'])) {
            $messageContent = "Phone: " . $validated['phone'] . "\n\n" . $messageContent;
        }

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? ($validated['phone'] ?? 'no-email@isaiahnailbar.com'),
            'message' => $messageContent,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you. Your message has been received! Our concierge team will reach out shortly.',
            'data' => $contact,
        ]);
    }

    /** Return the currently authenticated API-token user. */
    public function currentUser(Request $request)
    {
        $user = ApiToken::resolve($request->bearerToken() ?: $request->cookie('isaiah_api_token'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication is required.',
            ], 401);
        }

        $customer = Customer::where('email', $user->email)->first();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $customer?->phone ?? '',
                'role' => $user->roles()->exists() ? $user->roles->first()->name : 'customer',
                'customer_id' => $customer?->id ?? null,
            ],
        ]);
    }

    /**
     * User Login API
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 422);
        }

        $customer = Customer::where('email', $user->email)->first();

        $token = ApiToken::issue($user);

        return response()->json([
            'success' => true,
            'message' => 'Welcome back, ' . $user->name,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $customer?->phone ?? '',
                'role' => method_exists($user, 'roles') && $user->roles()->exists() ? $user->roles->first()->name : 'customer',
                'customer_id' => $customer?->id ?? null,
            ],
        ])->cookie('isaiah_api_token', $token, 8 * 60, '/', null, $request->isSecure(), true, false, 'lax');
    }

    public function logout(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Signed out.'])
            ->cookie('isaiah_api_token', '', -1, '/', null, $request->isSecure(), true, false, 'lax');
    }

    /**
     * User Registration API
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '',
        ]);

        $user->customer_id = $customer->id;
        $user->save();

        try {
            $user->assignRole('customer');
        } catch (\Exception $e) {
            // Ignore if role not defined
        }

        $token = ApiToken::issue($user);

        return response()->json([
            'success' => true,
            'message' => 'Welcome to Isaiah Nail Bar, ' . $user->name,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $customer->phone ?? '',
                'role' => 'customer',
                'customer_id' => $customer->id,
            ],
        ])->cookie('isaiah_api_token', $token, 8 * 60, '/', null, $request->isSecure(), true, false, 'lax');
    }

    /**
     * Customer Dashboard Bookings API
     */
    public function customerBookings(Request $request)
    {
        $user = $request->user();
        $customer = $user?->customer_id
            ? Customer::find($user->customer_id)
            : Customer::where('email', $user?->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found.',
                'bookings' => [],
                'stats' => [
                    'total_rituals' => 0,
                    'completed_rituals' => 0,
                    'sanctuary_points' => 0,
                    'tier' => 'Sanctuary Guest',
                    'total_spent' => 0,
                ],
            ]);
        }

        $allBookings = Booking::where('customer_id', $customer->id)
            ->with(['services', 'provider', 'payments'])
            ->latest('date')
            ->get();

        $today = now()->toDateString();

        $formattedBookings = $allBookings->map(function ($booking) use ($today) {
            $serviceTotal = (float) $booking->services->sum(
                fn ($service) => (float) ($service->pivot?->price_at_booking ?? $service->price)
            );
            $totalPrice = ($serviceTotal * ($booking->is_home_service ? 2 : 1)) + (float) $booking->pickup_fee;
            $totalPaid = $booking->payments->where('status', 'paid')->sum('amount');
            $balance = max(0, $totalPrice - $totalPaid);
            $isPast = $booking->date < $today || in_array($booking->status, ['completed', 'cancelled', 'declined']);

            return [
                'id' => $booking->id,
                'reference' => $booking->reference ?: 'INB-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                'date' => $booking->date,
                'time' => $booking->time ? substr($booking->time, 0, 5) : '10:00',
                'status' => $booking->status,
                'is_home_service' => (bool)$booking->is_home_service,
                'notes' => $booking->notes,
                'is_past' => $isPast,
                'services' => $booking->services->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => (float) ($s->pivot?->price_at_booking ?? $s->price),
                    'duration_minutes' => (int) ($s->pivot?->duration_minutes_at_booking ?? $s->duration_minutes),
                ]),
                'provider' => $booking->provider ? [
                    'id' => $booking->provider->id,
                    'name' => $booking->provider->name,
                ] : [
                    'id' => 1,
                    'name' => 'Sanctuary Master Stylist',
                ],
                'payments' => $booking->payments->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => $p->amount,
                    'method' => $p->method,
                    'status' => $p->status,
                    'reference' => $p->reference,
                    'paid_at' => $p->paid_at,
                ]),
                'total_price' => $totalPrice,
                'total_paid' => $totalPaid,
                'balance' => $balance,
                'payment_status' => $balance === 0 ? 'paid_in_full' : ($totalPaid > 0 ? 'deposit_paid' : 'unpaid'),
            ];
        });

        $completedCount = $formattedBookings->where('status', 'completed')->count();
        $totalSpent = $formattedBookings->sum('total_paid');
        $points = (int)($totalSpent / 500); // 1 point per 500 RWF

        $tier = 'Sanctuary Guest';
        if ($completedCount >= 5 || $totalSpent >= 200000) {
            $tier = 'Platinum Patron';
        } elseif ($completedCount >= 2 || $totalSpent >= 70000) {
            $tier = 'Gold Sanctuary Member';
        }

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
            ],
            'stats' => [
                'total_rituals' => $formattedBookings->count(),
                'completed_rituals' => $completedCount,
                'sanctuary_points' => $points,
                'tier' => $tier,
                'total_spent' => $totalSpent,
            ],
            'bookings' => $formattedBookings->values(),
        ]);
    }

    /**
     * Cancel Booking by Customer API
     */
    public function cancelCustomerBooking(Request $request, $id)
    {
        $customerId = $request->user()->customer_id;
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found.'], 404);
        }
        $booking = Booking::where('customer_id', $customerId)->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found.',
            ], 404);
        }

        if ($booking->status !== 'pending' && $booking->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'This appointment can no longer be cancelled online. Please call our concierge.',
            ], 422);
        }

        $booking->status = 'cancelled';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Appointment successfully cancelled.',
            'booking_id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Download Official PDF Receipt
     */
    public function downloadCustomerReceiptPdf(Request $request, $id)
    {
        $customerId = $request->user()->customer_id;
        if (!$customerId) {
            abort(404, 'Customer profile not found');
        }
        $booking = Booking::with(['service.category', 'services.category', 'provider', 'customer.user', 'payments'])
            ->where('customer_id', $customerId)
            ->find($id);

        if (!$booking) {
            abort(404, 'Booking receipt not found');
        }

        $logoPath = public_path('storage/logo.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadHTML(PdfTemplates::receipt($booking, $logo))->setPaper('a4');

        $ref = $booking->reference ?: 'INB-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
        return $pdf->download("isaiah-receipt-{$ref}.pdf");
    }

    /**
     * Update Customer Profile (Name & Phone)
     */
    public function updateCustomerProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = $request->user();
        if ($user) {
            $user->name = $validated['name'];
            $user->save();
        }

        $customer = $user?->customer_id ? Customer::find($user->customer_id) : Customer::where('email', $user?->email)->first();
        if ($customer) {
            $customer->name = $validated['name'];
            $customer->phone = $validated['phone'] ?? '';
            $customer->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sanctuary profile updated successfully.',
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? $validated['name'],
                'email' => $user?->email,
                'phone' => $customer?->phone ?? '',
            ],
        ]);
    }

}
