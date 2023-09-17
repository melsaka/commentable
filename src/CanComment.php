<?php

namespace Melsaka\Commentable;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Melsaka\Commentable\Helpers\ModelRelations;
use Melsaka\Commentable\Models\Comment;
use Illuminate\Database\Eloquent\Model;

trait CanComment
{
    use ModelRelations;

    private $commentMorph = 'owner';

    public function commentsAreAccepted(): bool
    {
        return false;
    }

    public function addComment($data, Model $commentable, ?Comment $parent = null): Comment
    {
        return Comment::for($commentable)->via($this)->add($data, $parent);
    }

    public function editComment($comment, $data, $parent = null): bool
    {
        $comment = Comment::getCommentOfId($comment);

        if ($comment && $this->commented($comment)) {
            return (bool) Comment::edit($comment, $data, $parent);
        }
        
        return false;
    }

    public function removeComment($comment): bool
    {
        $comment = Comment::getCommentOfId($comment);
        
        if ($comment && $this->commented($comment)) {
            return Comment::remove($comment);
        }

        return false;
    }

    public function deleteComment($comment): bool
    {
        return $this->removeComment($comment);
    }

    public function scopeCommentsOn($query, Model $commentable): MorphMany
    {
        return $this->comments()->where(Comment::morphsArray($commentable));
    }

    public function hasCommentsOn(Model $commentable): bool
    {
        return $this->comments()->where(Comment::morphsArray($commentable))->exists();
    }

    public function commented($comment): bool
    {
        return $this->id === $comment->owner_id;
    }
}
