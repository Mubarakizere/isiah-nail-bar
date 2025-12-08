<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Service;
use App\Models\ServiceTag;
use App\Models\Category;

class ServiceController extends Controller
{
    public function index(Request $request)
{
    $query = Service::query()->with('category');

    if ($request->filled('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->filled('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    if ($request->filled('status')) {
        if ($request->status === 'approved') {
            $query->where('approved', 1);
        } elseif ($request->status === 'rejected') {
            $query->where('approved', 0);
        } elseif ($request->status === 'pending') {
            $query->whereNull('approved');
        }
    }

    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $services = $query->latest()->paginate(10)->withQueryString();
    $statuses = ['pending', 'approved', 'rejected'];
    $categories = Category::orderBy('name')->get();

    return view('admin.services.index', compact('services', 'statuses', 'categories'));
}


    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|string',
            'image' => 'nullable|mimetypes:image/jpeg,image/png,image/webp,image/bmp,image/gif,image/svg+xml,image/tiff|max:20480', // 20MB
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('services', 'public');
        }

        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => (int) $request->duration_minutes,
            'category_id' => $request->category_id,
            'image' => $imageName,
        ]);

        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            foreach ($tags as $tag) {
                ServiceTag::create([
                    'service_id' => $service->id,
                    'tag' => $tag,
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $tags = $service->tags->pluck('tag')->implode(', ');
        $categories = Category::orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'tags', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|string',
            'image' => 'nullable|mimetypes:image/jpeg,image/png,image/webp,image/bmp,image/gif,image/svg+xml,image/tiff|max:20480', // 20MB
        ]);

        $data = $request->only(['name', 'description', 'price', 'duration_minutes', 'category_id']);

        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('services', 'public');
            $data['image'] = $imageName;

            if ($service->image && Storage::exists('public/' . $service->image)) {
                Storage::delete('public/' . $service->image);
            }
        }

        $service->update($data);

        // Sync tags
        $service->tags()->delete();
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            foreach ($tags as $tag) {
                ServiceTag::create([
                    'service_id' => $service->id,
                    'tag' => $tag,
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        if ($service->image && Storage::exists('public/' . $service->image)) {
            Storage::delete('public/' . $service->image);
        }

        $service->tags()->delete();
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        $reviews = $service->reviews()->with('customer')->latest()->get();

        $averageRating = $reviews->count()
            ? round($reviews->avg('rating'), 1)
            : 0;

        return view('dashboard.admin.services.show', compact('service', 'reviews', 'averageRating'));
    }
}
