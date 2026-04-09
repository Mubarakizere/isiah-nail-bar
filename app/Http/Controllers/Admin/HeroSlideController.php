<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::ordered()->get();
        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero-slides.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'secondary_button_text' => 'nullable|string|max:100',
            'secondary_button_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('hero-slides', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['button_text'] = $validated['button_text'] ?? 'Book Appointment';
        $validated['button_url'] = $validated['button_url'] ?? '/booking';

        HeroSlide::create($validated);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide created successfully.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'secondary_button_text' => 'nullable|string|max:100',
            'secondary_button_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($heroSlide->image && Storage::disk('public')->exists($heroSlide->image)) {
                Storage::disk('public')->delete($heroSlide->image);
            }
            $validated['image'] = $request->file('image')->store('hero-slides', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $heroSlide->update($validated);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide updated successfully.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        if ($heroSlide->image && Storage::disk('public')->exists($heroSlide->image)) {
            Storage::disk('public')->delete($heroSlide->image);
        }

        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide deleted successfully.');
    }

    public function toggleActive(HeroSlide $heroSlide)
    {
        $heroSlide->update(['is_active' => !$heroSlide->is_active]);

        return back()->with('success', 'Slide status updated.');
    }
}
