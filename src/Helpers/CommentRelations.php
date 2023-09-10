<?php

namespace Melsaka\Commentable\Helpers;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Melsaka\Commentable\Models\Comment;

trait CommentRelations
{
    /**
     * Get the commentable model (ex: Post, Product, etc..).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the owner model (ex: User).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent comment if comments has parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the comment replies if comment has replies.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Eager Load

    public function loadReplies($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'load');
    }

    public function loadRepliesCount($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'loadCount');
    }

    public function loadAcceptedReplies($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'load', 'onlyAccepted');
    }

    public function loadRejectedReplies($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'load', 'onlyRejected');
    }

    public function loadAcceptedRepliesCount($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'loadCount', 'onlyAccepted');
    }

    public function loadRejectedRepliesCount($callback = null): Model
    {
        return $this->eagerLoadRepliesProccess($callback, 'loadCount', 'onlyRejected');
    }

    public function scopeWithReplies($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'with');
    }

    public function scopeWithRepliesCount($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'withCount');
    }

    public function scopeWithAcceptedReplies($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'with', 'onlyAccepted');
    }

    public function scopeWithAcceptedRepliesCount($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'withCount', 'onlyAccepted');
    }

    public function scopeWithRejectedReplies($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'with', 'onlyRejected');
    }

    public function scopeWithRejectedRepliesCount($query, $callback = null): Builder
    {
        return $this->eagerWithRepliesProccess($query, $callback, 'withCount', 'onlyRejected');
    }

    private function eagerWithRepliesProccess($query, $callback, $method = 'with',  $only = null): Builder
    {
        return $query->{$method}(['replies' => function ($q) use ($callback, $only) {
            if ($only) {
                $q->{$only}();
            }

            if ($callback && is_callable($callback)) {
                $callback($q);
            }
        }]);
    }

    private function eagerLoadRepliesProccess($callback, $method = 'load',  $only = null): Model
    {
        return $this->{$method}(['replies' => function ($q) use ($callback, $only) {
            if ($only) {
                $q->{$only}();
            }

            if ($callback && is_callable($callback)) {
                $callback($q);
            }
        }]);
    }
}
