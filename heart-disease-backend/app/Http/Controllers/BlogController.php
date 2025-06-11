<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $blogPosts = BlogPost::with('author')->get();
        return response()->json($blogPosts);
    }

    public function show($id)
    {
        $blogPost = BlogPost::with('author')->find($id);
        if (!$blogPost) {
            return response()->json(['error' => 'Blog post not found'], 404);
        }
        return response()->json($blogPost);
    }
}
