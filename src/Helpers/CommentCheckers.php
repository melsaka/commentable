<?php

namespace Melsaka\Commentable\Helpers;

trait CommentCheckers
{
    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }

    public function hasParent(): bool
    {
        return $this->parent_id !== null;
    }
}
