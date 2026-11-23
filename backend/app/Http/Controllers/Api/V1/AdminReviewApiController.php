<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminReviewApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::query()->with(['service:id,name', 'customer.user'])->latest();
        $search = trim((string) $request->query('search', ''));
        $source = $request->query('source');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('reviewer_name', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('customer.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if (in_array($source, ['google', 'internal'], true)) {
            $query->where('source', $source);
        }

        $reviews = $query->paginate(10);
        $allReviews = Review::query();

        return response()->json([
            'success' => true,
            'data' => $reviews->getCollection()->map(fn (Review $review) => $this->serializeReview($review))->values(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'from' => $reviews->firstItem(),
                'to' => $reviews->lastItem(),
            ],
            'stats' => [
                'total_reviews' => (clone $allReviews)->count(),
                'google_reviews' => (clone $allReviews)->where('source', 'google')->count(),
                'average_rating' => round((float) (clone $allReviews)->avg('rating'), 1),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string'],
            'source' => ['required', 'in:google,internal'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
            'review_date' => ['nullable', 'date'],
        ]);

        $review = Review::create([
            'reviewer_name' => trim($validated['reviewer_name']),
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'source' => $validated['source'],
            'avatar_url' => $validated['avatar_url'] ?? null,
        ]);

        if (!empty($validated['review_date'])) {
            $review->created_at = Carbon::parse($validated['review_date'])->startOfDay();
            $review->save();
        }

        $review->load(['service:id,name', 'customer.user']);

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully.',
            'data' => $this->serializeReview($review),
        ], 201);
    }

    public function update(Request $request, Review $review)
    {
        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string'],
            'source' => ['required', 'in:google,internal'],
            'avatar_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $review->update([
            'reviewer_name' => trim($validated['reviewer_name']),
            'rating' => $validated['rating'],
            'comment' => trim($validated['comment']),
            'source' => $validated['source'],
            'avatar_url' => $validated['avatar_url'] ?? null,
        ]);
        $review->load(['service:id,name', 'customer.user']);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully.',
            'data' => $this->serializeReview($review),
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
        ]);
    }

    private function serializeReview(Review $review): array
    {
        $customerUser = $review->customer?->user;
        $photo = $customerUser?->photo;
        $avatar = $review->avatar_url;

        if (!$avatar && $photo) {
            $avatar = str_starts_with($photo, 'http') ? $photo : '/storage/' . ltrim($photo, '/');
        }

        return [
            'id' => $review->id,
            'reviewer_name' => $review->reviewer_name ?: ($customerUser?->name ?: 'Anonymous'),
            'reviewer_email' => $customerUser?->email,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'source' => $review->source ?: 'internal',
            'avatar_url' => $avatar,
            'service_name' => $review->service?->name,
            'created_at' => $review->created_at?->toISOString(),
            'created_at_human' => $review->created_at?->diffForHumans(),
        ];
    }
}
