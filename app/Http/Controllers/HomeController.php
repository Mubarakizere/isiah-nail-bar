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

        // Reviews from Database
        $reviews = \App\Models\Review::latest()->take(10)->get();

        return view('home.index', compact('services', 'featuredServices', 'reviews'));
    }
}
