<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index() {
        $blogs = Blog::where('is_published', true)->paginate(10);
        return BlogResource::collection($blogs);
    }

    public function show($slug){
        $blog = Blog::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return new BlogResource($blog);
    }
}
