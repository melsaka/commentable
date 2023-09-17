<?php

namespace Melsaka\Commentable\Helpers;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Melsaka\Commentable\Models\Comment;
use Illuminate\Database\Eloquent\Model;

trait ModelRelations
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, $this->commentMorph);
    }

    public function replies(): MorphMany
    {
        return $this->morphMany(Comment::class, $this->commentMorph)->whereNotNull('parent_id');
    }

    // Eager Load
    
    public function loadComments($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'load');
    }

    public function loadCommentsCount($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'loadCount');
    }

    public function loadAcceptedComments($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'load', 'onlyAccepted');
    }

    public function loadAcceptedCommentsCount($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'loadCount', 'onlyAccepted');
    }

    public function loadRejectedComments($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'load', 'onlyRejected');
    }

    public function loadRejectedCommentsCount($callback = null): Model
    {
        return $this->eagerLoadCommentsProccess($callback, 'loadCount', 'onlyRejected');
    }

    public function scopeWithComments($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'with');
    }

    public function scopeWithCommentsCount($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'withCount');
    }

    public function scopeWithAcceptedComments($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'with', 'onlyAccepted');
    }

    public function scopeWithAcceptedCommentsCount($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'withCount', 'onlyAccepted');
    }

    public function scopeWithRejectedComments($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'with', 'onlyRejected');
    }

    public function scopeWithRejectedCommentsCount($query, $callback = null): Builder
    {
        return $this->eagerWithCommentsProccess($query, $callback, 'withCount', 'onlyRejected');
    }

    private function eagerWithCommentsProccess($query, $callback, $method = 'with', $only = null): Builder
    {
        return $query->{$method}(['comments' => function ($q) use ($callback, $only) {
            if ($only) {
                $q->{$only}();
            }

            if ($callback && is_callable($callback)) {
                $callback($q);
            }

            $q->whereNull('parent_id');
        }]);
    }

    private function eagerLoadCommentsProccess($callback, $method = 'load', $only = null): Model
    {
        return $this->{$method}(['comments' => function ($q) use ($callback, $only) {
            if ($only) {
                $q->{$only}();
            }

            if ($callback && is_callable($callback)) {
                $callback($q);
            }
            
            $q->whereNull('parent_id');
        }]);
    }
}
