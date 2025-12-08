<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class CustomerReviewController extends Controller
{
    public function index()
{
    $customerId = Auth::user()->customer_id;

    $reviews = Review::with(['service'])
        ->where('customer_id', $customerId)
        ->latest()
        ->paginate(10);

    return view('dashboard.customer.reviews.index', compact('reviews'));
}

    // CustomerReviewController
public function destroy($id)
{
    $review = Review::findOrFail($id);

    if (Auth::user()->role !== 'admin' && $review->customer_id !== Auth::user()->customer_id) {
        abort(403);
    }

    $review->delete();

    return redirect()->route('customer.reviews.index')->with('success', 'Review deleted.');
}


}
