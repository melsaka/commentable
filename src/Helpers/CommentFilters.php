<?php

namespace Melsaka\Commentable\Helpers;

use Illuminate\Database\Eloquent\Builder;

trait CommentFilters
{
    public function scopeOnlyParents($query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOnlyAccepted($query): Builder
    {
        return $query->where('accepted', true);
    }

    public function scopeOnlyRejected($query): Builder
    {
        return $query->where('accepted', false);
    }

    public function scopeOnlyRated($query): Builder
    {
        return $query->whereNotNull('rate');
    }

    public function scopeOnlyNotRated($query): Builder
    {
        return $query->whereNull('rate');
    }
}
