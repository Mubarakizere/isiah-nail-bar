<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Notifications\ServiceStatusNotification;
use App\Support\StorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminServiceApiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:all,pending,approved,rejected'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Service::query()->with(['category:id,name', 'tags']);

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($services) use ($term) {
                $services->where('name', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        match ($filters['status'] ?? 'all') {
            'pending' => $query->whereNull('approved'),
            'approved' => $query->where('approved', true),
            'rejected' => $query->where('approved', false),
            default => null,
        };

        $services = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $services->getCollection()
                ->map(fn (Service $service) => $this->serializeService($service))
                ->values(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ],
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : null;

        $service = Service::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'category_id' => $validated['category_id'],
            'image' => $imagePath,
            'approved' => true,
        ]);

        $this->syncTags($service, $validated['tags'] ?? '');
        $service->load(['category:id,name', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => $this->serializeService($service),
        ], 201);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate($this->rules());
        $oldImagePath = $service->image;
        $newImagePath = $request->hasFile('image')
            ? $request->file('image')->store('services', 'public')
            : null;

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'price' => $validated['price'],
            'duration_minutes' => $validated['duration_minutes'],
            'category_id' => $validated['category_id'],
            ...($newImagePath ? ['image' => $newImagePath] : []),
        ]);

        if ($newImagePath && $oldImagePath && $oldImagePath !== $newImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->syncTags($service, $validated['tags'] ?? '');
        $service->load(['category:id,name', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => $this->serializeService($service),
        ]);
    }

    public function destroy(Service $service)
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->tags()->delete();
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully.',
        ]);
    }

    public function approve(Service $service)
    {
        $service->update(['approved' => true]);
        $service->provider?->user?->notify(
            new ServiceStatusNotification($service->name, 'approved')
        );

        return response()->json([
            'success' => true,
            'message' => 'Service approved.',
        ]);
    }

    public function reject(Service $service)
    {
        $service->update(['approved' => false]);
        $service->provider?->user?->notify(
            new ServiceStatusNotification($service->name, 'rejected')
        );

        return response()->json([
            'success' => true,
            'message' => 'Service declined.',
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'image' => [
                'nullable',
                'mimetypes:image/jpeg,image/png,image/webp,image/bmp,image/gif,image/tiff',
                'max:20480',
            ],
        ];
    }

    private function syncTags(Service $service, string $tags): void
    {
        $service->tags()->delete();

        $values = collect(explode(',', $tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->map(fn (string $tag) => ['tag' => $tag])
            ->all();

        if ($values) {
            $service->tags()->createMany($values);
        }
    }

    private function serializeService(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'price' => (float) $service->price,
            'duration_minutes' => (int) $service->duration_minutes,
            'category_id' => $service->category_id,
            'category' => $service->category
                ? ['id' => $service->category->id, 'name' => $service->category->name]
                : null,
            'image_url' => StorageUrl::format($service->image),
            'tags' => $service->tags->pluck('tag')->values(),
            'approved' => $service->approved,
            'status' => is_null($service->approved)
                ? 'pending'
                : ($service->approved ? 'approved' : 'rejected'),
            'created_at' => $service->created_at?->toISOString(),
            'created_ago' => $service->created_at?->diffForHumans(),
        ];
    }
}
