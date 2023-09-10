<?php

namespace Melsaka\Commentable\Models;

use Melsaka\Commentable\Helpers\CommentRelations;
use Melsaka\Commentable\Helpers\CommentCheckers;
use Melsaka\Commentable\Helpers\CommentActions;
use Melsaka\Commentable\Helpers\CommentGetters;
use Melsaka\Commentable\Helpers\CommentFilters;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use CommentRelations;
    use CommentCheckers;
    use CommentActions;
    use CommentGetters;
    use CommentFilters;

    private $commentData;

    private $forModel;

    private $viaModel;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function getTable()
    {
        $tableName = config('commentable.table', 'comments');

        return $tableName;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            Comment::where('parent_id', $model->id)->delete();
        });
    }
}
