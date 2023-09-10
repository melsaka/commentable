<?php

namespace Melsaka\Commentable;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Melsaka\Commentable\Helpers\ModelRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Melsaka\Commentable\Models\Comment;

trait HasComments
{
    use ModelRelations;

    private $morphName = 'commentable';

    public function commentsAreRated(): bool
    {
        return false;
    }

    public function commentsAreAccepted(): bool
    {
        return true;
    }

    public function addComment($data, Model $owner, ?Comment $parent = null): Comment
    {
        return Comment::for($this)->via($owner)->add($data, $parent);
    }

    public function editComment($comment, $data, $parent = null): bool
    {
        $comment = Comment::getCommentOfId($comment);

        if ($comment && $this->hasComment($comment)) {
            return (bool) Comment::edit($comment, $data, $parent);
        }

        return false;
    }

    public function removeComment($comment): bool
    {
        $comment = Comment::getCommentOfId($comment);
        
        if ($comment && $this->hasComment($comment)) {
            return Comment::remove($comment);
        }

        return false;
    }

    public function deleteComment($comment): bool
    {
        return $this->removeComment($comment);
    }

    public function acceptComment($comment): bool
    {
        $comment = Comment::getCommentOfId($comment);

        if ($comment && $this->hasComment($comment)) {
            return $comment->accept();
        }

        return false;
    }

    public function rejectComment($comment): bool
    {
        $comment = Comment::getCommentOfId($comment);

        if ($comment && $this->hasComment($comment)) {
            return $comment->reject();
        }

        return false;
    }

    public function rateComment($comment, $rate): bool
    {
        $comment = Comment::getCommentOfId($comment);

        if ($this->commentsAreRated() && $comment && $this->hasComment($comment)) {
            return $comment->rateIt($rate);
        }

        return false;
    }

    public function scopeCommentsBy($query, Model $owner): MorphMany
    {
        return $this->comments()->where($owner->morphsArray());
    }

    public function scopeAcceptedComments($query): Builder
    {
        return $this->comments()->onlyAccepted();
    }

    public function hasCommentsBy(Model $owner): bool
    {
        return $this->comments()->where($owner->morphsArray())->exists();
    }
    
    public function hasComment($comment): bool
    {
        return $this->id === $comment->commentable_id;
    }

    public function averageRate(int $round = 2): float
    {
        if (!$this->commentsAreRated()) {
            return 0;
        }

        /** @var Builder $rates */
        $rates = $this->acceptedComments();

        if (!$rates->exists()) {
            return 0;
        }

        return round((float) $rates->avg('rate'), $round);
    }
}
