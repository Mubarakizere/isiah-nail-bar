<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Review, Service, Booking, User};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Notifications\NewReviewNotification;

class ReviewController extends Controller
{
    // Show review form for a completed booking
    public function create(Request $request)
    {
        $bookingId = $request->query('booking_id');

        $booking = Booking::with('services')
            ->where('id', $bookingId)
            ->where('customer_id', auth()->user()->customer->id ?? null)
            ->first();

        return view('dashboard.reviews.create', compact('booking'));
    }

    // Store new review
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $user = Auth::user();
        $customer = $user->customer;

        // Check if customer completed this service in any booking
        $hasCompleted = $customer->bookings()
            ->where('status', 'completed')
            ->whereHas('services', function ($query) use ($request) {
                $query->where('service_id', $request->service_id);
            })
            ->exists();

        if (! $hasCompleted) {
            return redirect()->back()->with('error', 'You can only review services you have completed.');
        }

        // Prevent duplicate review
        $existingReview = Review::where('service_id', $request->service_id)
            ->where('customer_id', $customer->id)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('error', 'You have already reviewed this service.');
        }

        // Save review
        $review = Review::create([
            'service_id' => $request->service_id,
            'customer_id' => $customer->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Notify admins
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewReviewNotification($review));
        }

        return redirect()->back()->with('success', 'Thank you for your review!');
    }

    // Admin: list all reviews
    public function adminIndex()
    {
        $reviews = Review::with(['service', 'customer.user'])->latest()->paginate(10);
        return view('dashboard.admin.reviews.index', compact('reviews'));
    }

    // Admin: delete review
    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted successfully.');
    }

    // Optional: fetch Google reviews (if enabled)
    public function fetchGoogleReviews()
    {
        $apiKey = config('services.google_places.key');
        $placeId = config('services.google_places.place_id');

        $response = Http::get("https://maps.googleapis.com/maps/api/place/details/json", [
            'place_id' => $placeId,
            'fields' => 'reviews',
            'key' => $apiKey,
        ]);

        $reviews = $response->json('result.reviews') ?? [];

        return view('partials.google-reviews', compact('reviews'));
    }
}
