<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GalleryInstagram;
use Illuminate\Http\Request;

class AdminGalleryInstagramApiController extends Controller
{
    public function index()
    {
        $posts = GalleryInstagram::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $posts->map(fn (GalleryInstagram $post) => $this->serializePost($post))->values(),
            'stats' => [
                'total' => $posts->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:255', 'starts_with:https://www.instagram.com/'],
        ]);

        $post = GalleryInstagram::create(['url' => trim($validated['url'])]);

        return response()->json([
            'success' => true,
            'message' => 'Instagram post added to gallery.',
            'data' => $this->serializePost($post),
        ], 201);
    }

    public function destroy(GalleryInstagram $galleryInstagram)
    {
        $galleryInstagram->delete();

        return response()->json([
            'success' => true,
            'message' => 'Instagram post removed.',
        ]);
    }

    private function serializePost(GalleryInstagram $post): array
    {
        return [
            'id' => $post->id,
            'url' => $post->url,
            'created_at' => $post->created_at?->toISOString(),
        ];
    }
}
