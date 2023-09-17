<?php

namespace Melsaka\Commentable\Helpers;

use Illuminate\Database\Eloquent\Model;
use Melsaka\Commentable\Models\Comment;
use Illuminate\Support\Collection;

trait CommentActions
{
    public function scopeFor($query, Model $commentable): Comment
    {
        $this->forModel = $commentable;

        return $this;
    }

    public function scopeVia($query, Model $owner): Comment
    {
        $this->viaModel = $owner;

        return $this;
    }

    public function add($data, ?Comment $parent = null): Comment
    {
        $this->prepareComment($data)
                ->setCommentOwner($this->viaModel)
                ->setCommentRate($this->forModel)
                ->setCommentStatus($this->forModel, $this->viaModel);

        $comment = $this->forModel->comments()->create($this->commentData);

        $this->setCommentParent($comment, $parent);

        return $comment;
    }

    public function editTo($data, ?Comment $parent = null): Comment
    {
        return Comment::edit($this, $data, $parent);
    }

    public function scopeEdit($query, $comment, $data, ?Comment $parent = null): Comment
    {
        if (is_int($comment)) {
            $comment = static::getCommentOfId($comment);
        }
        
        if (!($comment instanceof Comment)) {
            throw new InvalidArgumentException('$comment must be a valid Comment instance.');
        }

        if (!$comment->id) {
            abort(404, 'Resource Not Found');
        }

        $validDataType = is_string($data) || is_array($data);

        if (!$validDataType && gettype($data) === 'object') {
            return $comment;
        }

        if (!$validDataType) {
            $data = (string) $data;
        }

        $this->forModel = $this->forModel ?: $comment->commentable;

        $this->prepareComment($data)
                ->setCommentRate($this->forModel)
                ->setCommentStatus($this->forModel);

        $comment->update($this->commentData);

        $this->setCommentParent($comment, $parent);

        return $comment;
    }

    public function scopeRemove($query, $comment = null): bool
    {
        if ($this->id) {
            return $query->where('id', $this->id)->orWhere('parent_id', $this->id)->delete();
        }

        if ($comment instanceof Collection && $comment->isEmpty()) {
            return false;
        }

        if (empty($comment)) {
            return false;
        }

        if ($comment instanceof Comment) {
            return $query->where('id', $comment->id)->orWhere('parent_id', $comment->id)->delete();
        }

        if ($comment instanceof Collection) {
            $ids = $comment->pluck('id')->toArray();

            return $query->whereIn('id', $ids)->orWhereIn('parent_id', $ids)->delete();
        }

        if (is_array($comment)) {
            return $query->whereIn('id', $comment)->orWhereIn('parent_id', $comment)->delete();
        }

        if (is_int($comment)) {
            return $query->where('id', $comment)->orWhere('parent_id', $comment)->delete();
        }

        return false;
    }

    public function scopeAccept($query, $comment = null): bool
    {
        $comment = $this->id ? $this : $comment;

        $comment = static::getCommentOfId($comment);

        if ($comment) {
            return $query->where('id', $comment->id)->update(['accepted' => true]);
        }

        return false;
    }

    public function scopeReject($query, $comment = null): bool
    {
        $comment = $this->id ? $this : $comment;

        $comment = static::getCommentOfId($comment);

        if ($comment) {
            return $query->where('id', $comment->id)->update(['accepted' => false]);
        }

        return false;
    }
     
    public function scopeRateIt($query, $rate, $comment = null): bool
    {
        $comment = $this->id ? $this : $comment;

        $comment = static::getCommentOfId($comment);

        if ($comment) {
            return $query->where('id', $comment->id)->update(['rate' => $rate]);
        }

        return false;
    }   

    public function addParent(Comment $parent): bool
    {
        if ($parent->parent_id) {
            return $this->parent()
                        ->associate($parent->parent_id)
                        ->save();
        }

        return $this->parent()
                    ->associate($parent)
                    ->save();
    }

    private function prepareComment($data, Comment $comment = null): Comment
    {
        $this->commentData = !is_array($data) ? ['body' => $data] : $data;

        if ($comment) {
            $this->commentData['rate'] = $comment->rate;
            $this->commentData['accepted'] = $comment->accepted;
        }

        return $this;
    }

    private function setCommentOwner(Model $owner): Comment
    {
        $this->commentData['owner_id'] = Comment::primaryId($owner);
        $this->commentData['owner_type'] = get_class($owner);

        return $this;
    }

    private function setCommentRate(Model $commentable): Comment
    {
        if (!$commentable->commentsAreRated() || !array_key_exists('rate', $this->commentData)) {
            $this->commentData['rate'] = null;

            return $this;
        }

        return $this;
    }

    private function setCommentStatus(Model $commentable, Model $owner = null): Comment
    {
        if (!array_key_exists('accepted', $this->commentData)) {
            $this->commentData['accepted'] = $commentable->commentsAreAccepted();
        }

        if($owner && $owner->commentsAreAccepted()) {
            $this->commentData['accepted'] = $owner->commentsAreAccepted();
        }

        return $this;
    }

    private function setCommentParent(Comment $comment, ?Comment $parent): bool
    {
        return $parent ? $comment->addParent($parent) : false;
    }
}
