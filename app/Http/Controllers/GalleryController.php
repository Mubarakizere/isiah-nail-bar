<?php

namespace App\Http\Controllers;

use App\Models\GalleryInstagram;

class GalleryController extends Controller
{
    public function index()
    {
        $posts = GalleryInstagram::latest()->get();
        return view('gallery.index', compact('posts'));
    }
}
