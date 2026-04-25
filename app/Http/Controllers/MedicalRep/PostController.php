<?php

namespace App\Http\Controllers\MedicalRep;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends BaseMedicalRepController
{
   
    public function index(): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $posts = Post::with('author')
            ->withCount(['comments', 'postLikes as likes_count'])
            ->orderByDesc('created_at')
            ->paginate(15);
        return $this->success(['posts' => $posts]);
    }

   
    public function store(Request $request): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['nullable', 'in:published,draft'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $post = Post::create([
            'author_type' => 'medical_rep',
            'author_id' => $rep->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => $validated['status'] ?? 'published',
        ]);

        return $this->success(['post' => $post], null, 201);
    }

   
    public function show(int $id): JsonResponse
    {
        $post = Post::with(['author', 'comments'])
            ->withCount(['postLikes as likes_count'])
            ->find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        return $this->success(['post' => $post]);
    }

    
    public function update(Request $request, int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        if ($post->author_type !== 'medical_rep' || (int) $post->author_id !== (int) $rep->id) {
            return $this->error('Forbidden', 403);
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', 'in:published,draft'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $text = $validated['content'] ?? $post->content;

        $post->update([
            'title' => $validated['title'],
            'content' => $text,
            'status' => $validated['status'] ?? $post->status,
        ]);
        return $this->success(['post' => $post->fresh()]);
    }

    
  
    public function destroy(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        if ($post->author_type !== 'medical_rep' || (int) $post->author_id !== (int) $rep->id) {
            return $this->error('Forbidden', 403);
        }

        $post->delete();
        return $this->success([], 'Post deleted');
    }

   
    public function storeComment(Request $request, int $postId): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        if (!Post::whereKey($postId)->exists()) {
            return $this->error('Post not found', 404);
        }

        $validated = $this->validateRequest($request, [
            'comment_text' => ['required', 'string'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $comment = Comment::create([
            'post_id' => $postId,
            'user_type' => 'medical_rep',
            'user_id' => $rep->id,
            'comment_text' => $validated['comment_text'],
        ]);

        Post::whereKey($postId)->increment('comments_count');
        return $this->success(['comment' => $comment], null, 201);
    }

   
    public function destroyComment(int $id): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $comment = Comment::find($id);
        if (!$comment) {
            return $this->error('Comment not found', 404);
        }
        if ($comment->user_type !== 'medical_rep' || (int) $comment->user_id !== (int) $rep->id) {
            return $this->error('Forbidden', 403);
        }

        $postId = $comment->post_id;
        $comment->delete();
        Post::whereKey($postId)->decrement('comments_count');
        return $this->success([], 'Comment deleted');
    }

   
    public function like(int $postId): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        if (!Post::whereKey($postId)->exists()) {
            return $this->error('Post not found', 404);
        }

        $exists = PostLike::where('post_id', $postId)->where('user_type', 'medical_rep')->where('user_id', $rep->id)->exists();
        if ($exists) {
            return $this->error('Already liked', 422);
        }

        PostLike::create([
            'post_id' => $postId,
            'user_type' => 'medical_rep',
            'user_id' => $rep->id,
        ]);
        Post::whereKey($postId)->increment('likes_count');

        return $this->success([], 'Post liked', 201);
    }

   
    public function unlike(int $postId): JsonResponse
    {
        $rep = $this->repOrForbidden();
        if ($rep instanceof JsonResponse) {
            return $rep;
        }

        $like = PostLike::where('post_id', $postId)->where('user_type', 'medical_rep')->where('user_id', $rep->id)->first();
        if (!$like) {
            return $this->error('Like not found', 404);
        }

        $like->delete();
        Post::whereKey($postId)->decrement('likes_count');
        return $this->success([], 'Post unliked');
    }
}
