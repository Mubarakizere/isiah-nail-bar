<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminTagApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Tag::query()->latest();
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tags->getCollection()->map(fn (Tag $tag) => $this->serializeTag($tag))->values(),
            'pagination' => [
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
                'from' => $tags->firstItem(),
                'to' => $tags->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => is_string($request->input('name'))
                ? trim($request->input('name'))
                : $request->input('name'),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
        ]);

        $tag = Tag::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Tag \"{$tag->name}\" created successfully.",
            'data' => $this->serializeTag($tag),
        ], 201);
    }

    public function update(Request $request, Tag $tag)
    {
        $request->merge([
            'name' => is_string($request->input('name'))
                ? trim($request->input('name'))
                : $request->input('name'),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);

        $tag->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Tag \"{$tag->name}\" updated successfully.",
            'data' => $this->serializeTag($tag->fresh()),
        ]);
    }

    public function destroy(Tag $tag)
    {
        $name = $tag->name;
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => "Tag \"{$name}\" deleted successfully.",
        ]);
    }

    private function serializeTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'created_at' => $tag->created_at?->toISOString(),
            'created_ago' => $tag->created_at?->diffForHumans(),
        ];
    }
}
