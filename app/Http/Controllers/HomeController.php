<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Service;
use App\Models\HeroSlide;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        // Random 3 services for the Featured section
        $featuredServices = Service::with('category')->inRandomOrder()->take(3)->get();

        // Optional: latest services for another section
        $services = Service::latest()->take(6)->get();

        // Reviews from Database
        $reviews = Review::latest()->take(10)->get();

        // SEO: Review aggregate stats for structured data
        $reviewCount = Review::count();
        $averageRating = $reviewCount > 0 ? round(Review::avg('rating'), 1) : 4.9;

        // Hero Slides for the carousel
        $heroSlides = HeroSlide::active()->ordered()->get();

        return view('home.index', compact(
            'services', 'featuredServices', 'reviews', 'heroSlides',
            'reviewCount', 'averageRating'
        ));
    }
}
