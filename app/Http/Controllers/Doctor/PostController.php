<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\PostShare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function index(): JsonResponse
    {
        $posts = Post::query()
            // ->where('status', 'published')
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
            ->withCount('comments as comments_count')
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

    public function report(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        PostReport::create([
            'post_id' => $post->id,
            'reporter_id' => $request->user()->id,
            'reporter_type' => 'doctor',
            'reason' => $validated['reason'] ?? null,
        ]);

        return $this->success([], 'Post reported', 201);
    }

    public function comment(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'content' => ['required', 'string', 'max:1000'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $comment = Comment::create([
            'post_id' => $id,
            'user_type' => 'doctor',
            'user_id' => $request->user()->id,
            'comment_text' => $validated['content'],
        ]);

        Post::whereKey($id)->increment('comments_count');
        return $this->success(['comment' => $comment], null, 201);
    }

    public function share(Request $request, int $id): JsonResponse
    {
        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $alreadyShared = PostShare::where('post_id', $id)
            ->where('sharer_id', $request->user()->id)
            ->where('sharer_type', 'doctor')
            ->exists();

        if ($alreadyShared) {
            return $this->error('Already shared', 409);
        }

        PostShare::create([
            'post_id' => $id,
            'sharer_id' => $request->user()->id,
            'sharer_type' => 'doctor',
        ]);

        return $this->success([], 'Post shared', 201);
    }
}
