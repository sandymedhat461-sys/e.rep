<?php

namespace App\Http\Controllers\Company;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends BaseCompanyController
{
    
    public function index(): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $posts = Post::with('author')->orderByDesc('created_at')->paginate(15);
        return $this->success(['posts' => $posts]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
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
            'author_type' => 'company',
            'author_id' => $company->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => $validated['status'] ?? 'published',
        ]);

        return $this->success(['post' => $post], null, 201);
    }

    
    public function show(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $post = Post::with(['author', 'comments'])->withCount('postLikes as likes_count')->find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        return $this->success(['post' => $post]);
    }

    
    public function update(Request $request, int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        if ($post->author_type !== 'company' || (int) $post->author_id !== (int) $company->id) {
            return $this->error('Forbidden', 403);
        }

        $validated = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['nullable', 'in:published,draft'],
        ]);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $post->update($validated);
        return $this->success(['post' => $post->fresh()]);
    }

    
    public function destroy(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $post = Post::find($id);
        if (!$post) {
            return $this->error('Post not found', 404);
        }
        if ($post->author_type !== 'company' || (int) $post->author_id !== (int) $company->id) {
            return $this->error('Forbidden', 403);
        }

        $post->delete();
        return $this->success([], 'Post deleted');
    }

    
    public function storeComment(Request $request, int $postId): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
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
            'user_type' => 'company',
            'user_id' => $company->id,
            'comment_text' => $validated['comment_text'],
        ]);

        Post::whereKey($postId)->increment('comments_count');
        return $this->success(['comment' => $comment], null, 201);
    }


    public function destroyComment(int $id): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $comment = Comment::find($id);
        if (!$comment) {
            return $this->error('Comment not found', 404);
        }
        if ($comment->user_type !== 'company' || (int) $comment->user_id !== (int) $company->id) {
            return $this->error('Forbidden', 403);
        }

        $postId = $comment->post_id;
        $comment->delete();
        Post::whereKey($postId)->decrement('comments_count');
        return $this->success([], 'Comment deleted');
    }

    
    public function like(int $postId): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        if (!Post::whereKey($postId)->exists()) {
            return $this->error('Post not found', 404);
        }

        $exists = PostLike::where('post_id', $postId)->where('user_type', 'company')->where('user_id', $company->id)->exists();
        if ($exists) {
            return $this->error('Already liked', 422);
        }

        PostLike::create([
            'post_id' => $postId,
            'user_type' => 'company',
            'user_id' => $company->id,
        ]);
        Post::whereKey($postId)->increment('likes_count');

        return $this->success([], 'Post liked', 201);
    }

    
    public function unlike(int $postId): JsonResponse
    {
        $company = $this->companyOrForbidden();
        if ($company instanceof JsonResponse) {
            return $company;
        }

        $like = PostLike::where('post_id', $postId)->where('user_type', 'company')->where('user_id', $company->id)->first();
        if (!$like) {
            return $this->error('Like not found', 404);
        }

        $like->delete();
        Post::whereKey($postId)->decrement('likes_count');
        return $this->success([], 'Post unliked');
    }
}
