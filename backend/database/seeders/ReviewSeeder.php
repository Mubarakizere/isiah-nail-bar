<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $reviews = [
            [
                'reviewer_name' => 'Lizzie',
                'rating' => 5,
                'comment' => "This nail salon is amazing. Worth the price as a bit more expensive than other salons but quality is unmatched.",
                'source' => 'google',
                'created_at' => now()->subMonths(6),
                'avatar_url' => null,
            ],
            [
                'reviewer_name' => 'Rebecca Duvert',
                'rating' => 5,
                'comment' => "Great service all around!",
                'source' => 'google',
                'created_at' => now()->subMonths(6),
            ],
            [
                'reviewer_name' => 'Joccoa Mbaye',
                'rating' => 5,
                'comment' => "The absolute best in town hands down. You can get intricate designs too. Great ambiance.",
                'source' => 'google',
                'created_at' => now()->subMonths(7),
            ],
            [
                'reviewer_name' => 'Mariam Vashakidze',
                'rating' => 5,
                'comment' => "Absolutely loved my nails! Isaiah is very professional! Highly recommended",
                'source' => 'google',
                'created_at' => now()->subMonths(8),
            ],
            [
                'reviewer_name' => 'Ishimwe Delice',
                'rating' => 5,
                'comment' => "Loved the nails and services",
                'source' => 'google',
                'created_at' => now()->subMonths(9),
                'avatar_url' => 'https://lh3.googleusercontent.com/a-/ALV-UjW_xY...', // Placeholder or leave null
            ],
            [
                'reviewer_name' => "Blush's Diary",
                'rating' => 5,
                'comment' => "Amazing service.. I loved it",
                'source' => 'google',
                'created_at' => now()->subMonths(9),
            ],
            [
                'reviewer_name' => 'nono Alistar',
                'rating' => 5,
                'comment' => "Best in city of kigali rw.He delivers the job correctly 🙏",
                'source' => 'google',
                'created_at' => now()->subMonths(9),
            ],
             [
                'reviewer_name' => 'Virginie Wartenhorst',
                'rating' => 5,
                'comment' => "Suuuuper friendly, the best at nail art, so patient and so talented! I'm obsessed with my nails.",
                'source' => 'google',
                'created_at' => now()->subMonths(10),
            ],
            [
                'reviewer_name' => 'Shaheen jeenathally',
                'rating' => 5,
                'comment' => "Very nice and professional service .One of the best in kigali for professional nails.",
                'source' => 'google',
                'created_at' => now()->subYear(),
            ],
            [
                'reviewer_name' => 'Kathrine janjak Hole',
                'rating' => 5,
                'comment' => "So kind and talented people, isaiah did an amazing job with design and shape. A perfectionist.",
                'source' => 'google',
                'created_at' => now()->subYear(),
            ],
             [
                'reviewer_name' => 'Guilaine Giramata',
                'rating' => 5,
                'comment' => "The best artist Isaie in the country 🔥🔥🔥💖",
                'source' => 'google',
                'created_at' => now()->subYear(),
            ],
            [
                'reviewer_name' => 'kanyambo arlene',
                'rating' => 5,
                'comment' => "He does magic thx a lot Isaiah🙏✌️",
                'source' => 'google',
                'created_at' => now()->subYear(),
            ],
        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }
    }
}
