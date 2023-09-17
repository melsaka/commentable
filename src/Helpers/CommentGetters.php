<?php

namespace Melsaka\Commentable\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Melsaka\Commentable\Models\Comment;

trait CommentGetters
{
    public function scopeOf($query, Model $commentable): Builder
    {
        return $query->where(Comment::morphsArray($commentable))->onlyParents()->withRepliesCount();
    }

    public function scopeBy($query, Model $owner): Builder
    {
        return $query->where(Comment::morphsArray($owner))->onlyParents()->withRepliesCount();
    }

    public static function getCommentOfId($comment): Comment
    {
        return $comment instanceof Comment ? $comment : Comment::where('id', $comment)->firstOrFail();
    }
}
