<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
   
    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->with('author')
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->success(['posts' => $posts]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:published,draft'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'author_type' => 'doctor',
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? null,
            'status' => $validated['status'],
        ]);

        return $this->success(['post' => $post], null, 201);
    }

   
    public function show(int $id): JsonResponse
    {
        $post = Post::query()
            ->with(['author', 'comments.user'])
            ->withCount('postLikes as likes_count')
            ->find($id);

        if (!$post) {
            return $this->error('Post not found', 404);
        }

        return $this->success(['post' => $post]);
    }

    
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        if ($post->author_type !== 'doctor' || (int) $post->author_id !== (int) $request->user()->id) {
            return $this->error('Forbidden', 403);
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:published,draft'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $validated['image'] ?? $post->image,
            'status' => $validated['status'],
        ]);

        return $this->success(['post' => $post->fresh()]);
    }


    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        if ($post->author_type !== 'doctor' || (int) $post->author_id !== (int) $request->user()->id) {
            return $this->error('Forbidden', 403);
        }

        $post->delete();

        return $this->success([], 'Post deleted');
    }
}
