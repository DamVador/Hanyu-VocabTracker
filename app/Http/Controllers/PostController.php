<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = false;

        if (auth()->check()) {
            $isAdmin = auth()->user()->hasRole('admin');
        }

        $search = $request->get('search');
        $posts = Post::with('author:id,username')
            ->latest()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();
            
        return Inertia::render('Blog/PostIndex', [
            'posts' => $posts,
            'isAdmin' => $isAdmin,
            'search' => $search,
        ]);
    }

    public function show(Post $post)
    {
        $post->load('author');
        return Inertia::render('Blog/PostShow', ['post' => $post]);
    }

    public function create()
    {
        return Inertia::render('Blog/PostCreate', [
            'tinymceApiKey' => config('tinymce.key')
        ]);
    }

    public function edit(Post $post)
    {
        return Inertia::render('Blog/PostEdit', [
            'post' => $post,
            'tinymceApiKey' => config('tinymce.key'),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($request->only('title', 'content'));

        return redirect()->route('blog.show', $post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $slug = Str::slug($validated['title']);
        $count = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = Str::slug($validated['title']) . '-' . $count++;
        }

        Post::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('blog.index');
    }
}
