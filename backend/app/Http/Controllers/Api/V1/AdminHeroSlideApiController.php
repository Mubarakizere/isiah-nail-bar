<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminHeroSlideApiController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $slides->map(fn (HeroSlide $slide) => $this->serializeSlide($slide))->values(),
            'stats' => [
                'total' => $slides->count(),
                'active' => $slides->where('is_active', true)->count(),
                'inactive' => $slides->where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(true));
        $validated['image'] = $request->file('image')->store('hero-slides', 'public');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['button_text'] = $validated['button_text'] ?: 'Book Appointment';
        $validated['button_url'] = $validated['button_url'] ?: '/booking';

        $slide = HeroSlide::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hero slide created successfully.',
            'data' => $this->serializeSlide($slide),
        ], 201);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $request->validate($this->rules(false));
        $oldImage = $heroSlide->image;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('hero-slides', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['button_text'] = $validated['button_text'] ?: ($heroSlide->button_text ?: 'Book Appointment');
        $validated['button_url'] = $validated['button_url'] ?: ($heroSlide->button_url ?: '/booking');

        $heroSlide->update($validated);

        if (isset($validated['image']) && $oldImage && $oldImage !== $validated['image']) {
            Storage::disk('public')->delete($oldImage);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hero slide updated successfully.',
            'data' => $this->serializeSlide($heroSlide->fresh()),
        ]);
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $image = $heroSlide->image;
        $heroSlide->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hero slide deleted successfully.',
        ]);
    }

    public function toggle(HeroSlide $heroSlide)
    {
        $heroSlide->update(['is_active' => !$heroSlide->is_active]);

        return response()->json([
            'success' => true,
            'message' => $heroSlide->is_active ? 'Hero slide activated.' : 'Hero slide deactivated.',
            'data' => $this->serializeSlide($heroSlide->fresh()),
        ]);
    }

    private function rules(bool $imageRequired): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:100'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function serializeSlide(HeroSlide $slide): array
    {
        return [
            'id' => $slide->id,
            'title' => $slide->title,
            'subtitle' => $slide->subtitle,
            'description' => $slide->description,
            'image' => $slide->image,
            'image_url' => StorageUrl::format($slide->image),
            'button_text' => $slide->button_text,
            'button_url' => $slide->button_url,
            'secondary_button_text' => $slide->secondary_button_text,
            'secondary_button_url' => $slide->secondary_button_url,
            'is_active' => (bool) $slide->is_active,
            'sort_order' => (int) $slide->sort_order,
            'created_at' => $slide->created_at?->toISOString(),
        ];
    }
}
