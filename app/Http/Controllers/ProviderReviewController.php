<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Review;

class ProviderReviewController extends Controller
{
    public function index()
    {
        $provider = Auth::user()->provider;

        $reviews = Review::whereHas('booking', function ($q) use ($provider) {
            $q->where('provider_id', $provider->id);
        })->with(['service', 'booking.customer.user'])->latest()->paginate(10);

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();
        $lastReviewDate = optional($reviews->first())->created_at;

        return view('dashboard.provider.reviews.index', compact(
            'reviews',
            'averageRating',
            'totalReviews',
            'lastReviewDate'
        ));
    }

}
