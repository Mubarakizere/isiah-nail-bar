<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryInstagram;

class GalleryInstagramController extends Controller
{
    public function index()
    {
        $posts = GalleryInstagram::latest()->get();
        return view('admin.gallery.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url|starts_with:https://www.instagram.com/',
        ]);

        GalleryInstagram::create(['url' => $request->url]);

        return back()->with('success', 'Instagram post added to gallery.');
    }

    public function destroy(GalleryInstagram $galleryInstagram)
    {
        $galleryInstagram->delete();
        return back()->with('success', 'Instagram post removed.');
    }
}
