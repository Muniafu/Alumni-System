<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumCategory;

class ForumCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ForumCategory::withCount(['threads', 'posts'])
            ->with('latestThread')
            ->orderBy('order')
            ->get();

        return view('forum.categories.index', compact('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ForumCategory $category)
    {
        $threads = $category->threads()
            ->with(['author', 'latestPost'])
            ->withCount('posts')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('forum.categories.show', compact('category', 'threads'));
    }
}
