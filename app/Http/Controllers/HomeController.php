<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Service;

class HomeController extends Controller
{
    public function index()
    {
        // Random 3 services for the Featured section
        $featuredServices = Service::with('category')->inRandomOrder()->take(3)->get();

        // Optional: latest services for another section
        $services = Service::latest()->take(6)->get();

        // Google Reviews API
        $apiKey = config('services.google_places.key');
        $placeId = config('services.google_places.place_id');

        $response = Http::get("https://maps.googleapis.com/maps/api/place/details/json", [
            'place_id' => $placeId,
            'fields' => 'reviews',
            'key' => $apiKey,
        ]);

        $reviews = $response->json('result.reviews') ?? [];

        return view('home.index', compact('services', 'featuredServices', 'reviews'));
    }
}
