<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Review, Service, Booking, User};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Notifications\NewReviewNotification;
use Carbon\Carbon;

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

    // Admin: show form to add a Google review
    public function adminCreate()
    {
        return view('dashboard.admin.reviews.create');
    }

    // Admin: store a manually-added Google review
    public function adminStore(Request $request)
    {
        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'source' => 'required|in:google,internal',
            'avatar_url' => 'nullable|url|max:2048',
            'review_date' => 'nullable|date',
        ]);

        $review = Review::create([
            'reviewer_name' => $request->reviewer_name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'source' => $request->source,
            'avatar_url' => $request->avatar_url,
        ]);

        // Backdate the created_at if a review date was provided
        if ($request->filled('review_date')) {
            $review->created_at = Carbon::parse($request->review_date);
            $review->save();
        }

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Google review added successfully!');
    }

    // Admin: show form to edit a review
    public function adminEdit(Review $review)
    {
        return view('dashboard.admin.reviews.edit', compact('review'));
    }

    // Admin: update a review
    public function adminUpdate(Request $request, Review $review)
    {
        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'source' => 'required|in:google,internal',
            'avatar_url' => 'nullable|url|max:2048',
        ]);

        $review->update([
            'reviewer_name' => $request->reviewer_name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'source' => $request->source,
            'avatar_url' => $request->avatar_url,
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review updated successfully!');
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
