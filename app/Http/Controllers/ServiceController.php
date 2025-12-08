<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index(Request $request)
{
    $categoryFilter = $request->category;
    $min = $request->min_price;
    $max = $request->max_price;
    $tag = $request->tag;

    // Get all unique tags
    $tags = Service::with('tags')->get()
        ->pluck('tags')
        ->flatten()
        ->pluck('tag')
        ->unique()
        ->sort()
        ->values();

    // ✅ Define strict order by category name
    $customOrder = [
        'Manicure',
        'Manicure Add-ons & Customization',
        'Pedicure',
        'Pedicure Add-ons',
    ];

    // 🧠 Fetch only these categories by name
    $rawCategories = Category::whereIn('name', $customOrder)
        ->with(['services' => function ($q) use ($min, $max, $tag) {
            $q->where('approved', 1);
            if ($min) $q->where('price', '>=', $min);
            if ($max) $q->where('price', '<=', $max);
            if ($tag) $q->whereHas('tags', fn($t) => $t->where('tag', $tag));
        }])
        ->get();

    // 🔁 Reorder based on $customOrder
    $categories = collect($customOrder)->map(function ($name) use ($rawCategories) {
        return $rawCategories->firstWhere('name', $name);
    })->filter(); // remove nulls

    return view('services.index', compact('categories', 'tags'));
}


}
