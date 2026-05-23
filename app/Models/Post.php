<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'author_type',
        'author_id',
        'title',
        'content',
        'image',
        'status',
        'likes_count',
        'comments_count',
        'shares_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function postLikes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    public function postShares(): HasMany
    {
        return $this->hasMany(PostShare::class);
    }
}
