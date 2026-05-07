<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $services = Service::where('approved', 1)
            ->select('id', 'name', 'updated_at')
            ->get();

        $categories = Category::select('id', 'name', 'updated_at')->get();

        $content = view('sitemap', compact('services', 'categories'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
