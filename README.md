# Commentable

The Commentable package for Laravel provides a convenient way to manage comments and comment-related functionality in your application. This documentation will guide you through the usage of this package.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Adding Comments](#adding-comments)
  - [Editing Comments](#editing-comments)
  - [Removing Comments](#removing-comments)
  - [Changing Comment Status](#changing-comment-status)
  - [Rating Comments](#rating-comments)
  - [Getters Methods](#getters-methods)
  - [Relation Methods](#relation-methods)
  - [Filters](#filters)
  - [Checkers](#checkers)
  - [Commentable Table](#commentable-table)
- [License](#license)

## Installation

Add the package to your Laravel app via Composer:

```bash
composer require melsaka/commentable
```

Register the package's service provider in config/app.php.

```php
'providers' => [
    ...
    Melsaka\Commentable\CommentableServiceProvider::class,
    ...
];
```

Run the migrations to add the required table to your database:

```bash
php artisan migrate
```

Add `CanComment` trait to the comment owner model, `User` model for example:

```php
use Melsaka\Commentable\CanComment;

class User extends Model
{
    use CanComment;
    
    // ...   
}
```

Add `HasComments` trait to your commentable model, `Post` model for example:

```php
use Melsaka\Commentable\HasComments;

class Post extends Model
{
    use HasComments;
    
    // ...   
}
```

If you want to enable `rating` feature in any **commentable** model (`Post` model in this case) add `commentsAreRated()` method.

By default, comments are not rated **false**. You can change this by adding this method and making it return **true**.

```php
class Post extends Model
{
    use HasComments;

    public function commentsAreRated(): bool
    {
        return true; // return false by default
    }

    // ..
}
```

By default, new comments are accepted, but sometimes you don't want to approve comments for all users;

In this case add `commentsAreAccepted()` method and make it return **false**, it return **true** by default.

```php
class Post extends Model
{
    use HasComments;

    public function commentsAreAccepted(): bool
    {
        return false; // return true by default
    }

    // ..
}
```

The owner model that uses the **CanComment** trait also has a `commentsAreAccepted()` method, which returns **false** by default. 

You can make this method return **true** for admin users only for emaple, so all admin users comments be accepted automatically.

```php
class User extends Model 
{
    use CanComment;
  
    protected $fillable = [
        'isAdmin',
        // ..
    ];

    public function commentsAreAccepted(): bool
    {
        return $this->isAdmin; // default false
    }

    // ..
}
```

## Configuration

To configure the package, publish its configuration file:

```bash
php artisan vendor:publish --tag=commentable
```

You can then modify the configuration file to change the comments table name if you want, default: `comments`.

## Usage

### Adding Comments

To add a new comment, you can use the `add` method after `for` and `via` methods like this:

```php
$post = Post::first();

$owner = User::first();

$parent = $post->comments()->first();

$data = [
    'body'      =>  'this ia a new reply',
    'accepted'  =>  true,
    'rate'      =>  4.5,
    'parent_id' =>  1,
];

Comment::for($post)->via($owner)->add($data, $parent);
```
- `$post` is the commentable model.

- `$owner` is the owner of the comment.

- `$data` can be a **string** or an **array** holding comment data.

- `$parent` is an optional parameter to add the comment as a reply to a parent comment, **default**: `null`.

**Note**: We may use `$post`, `$owner`, `$parent` variables in the upcoming examples. 

You can also add comments in a many different ways:

```php
$data = 'this is a new parent comment';

(new Comment)->for($post)->via($owner)->add($data);

$post->addComment($data, $owner);

$owner->addComment($data, $post);
```

**Note**: all these methods accept `$parent` param but it's `null` by default. so you can do this as well: 

```php
$post->addComment($data, $post, $parent);
```

### Editing Comments

To edit a comment, use the `edit` method and pass the `$comment` you want to edit as the first parameter:

```php
Comment::for($post)->edit($comment, $data, $parent);
```

- `$comment` can be the comment's ID, or the comment itself.

- `$data` can be a **string** or an **array** containing the updated comment data.

- `$parent` is optional and has a **default** value of **null**.

You can also edit comments using different methods:

```php
$comment->for($post)->editTo($data);

$post->editComment($comment, $data);

$owner->editComment($comment, $data);
```

In this example: `$comment->for($post)->editTo($data);`, The **for** method reduces the queries executed. 

But you can edit comments **without it** since we fetch the **commentable** model anyway, if you don't provide it in a **for** method.

Also the `editComment` method won't update, if comment `commentable_id` or `owner_id` is not the same as `$post->id` or `$owner->id`.

**Note**: all these methods accept `$parent` param but it's `null` by default. so you can do this as well: 

```php
$post->editComment($data, $post, $parent);
```

You can also add a **parent** to a comment using `addParent()` method

```php
$comment->addParent($parent);
```

### Removing Comments

To remove **one** or **more** comments, use the `remove` method:

```php
Comment::remove($comment);
```

- `$comment` can be a **Comment** instance, a **collection** of comments, an **array** of comment IDs, or a single comment **ID**.

To remove a single comment instance, you can also use:

```php
$comment->remove();
```

You can remove comments in different ways:

```php
$post->removeComment($comment);

$post->deleteComment($comment);

$owner->removeComment($comment);

$owner->deleteComment($comment);
```

The `deleteComment` and `removeComment` methods won't remove `$comment`, if comment `commentable_id` or `owner_id` is not the same as `$post->id` or `$owner->id`.

### Changing Comment Status

You can change the comment status to accepted using the `accept` method:

```php
$comment->accept();
```

To reject a comment, use the `reject` method:

```php
$comment->reject();
```

You can also accept or reject comments on a post:

```php
$post->acceptComment($comment);

$post->rejectComment($comment);
```

The `acceptComment` and `rejectComment` methods won't update `$comment` status, if comment `commentable_id` or `owner_id` is not the same as `$post->id` or `$owner->id`.

### Rating Comments

To change the rating of a comment, use the `rateIt` method:

```php
$comment->rateIt($rate);
```

You can also rate a comment using these methods:

```php
Comment::rateIt($rate, $comment);

$post->rateComment($comment, $rate);
```

`rateComment` returns **false** if `$post` `commentsAreRated` is **false** or `commentable_id` is not the same as `$post->id`.

You can also get the post average rate using `averageRate()` method.

```php
$post->averageRate();
```

### Getters Methods

To get comment by id, you can use `Comment::getCommentOfId($id)` it returns 404 if id not exist.

```php
Comment::getCommentOfId($id); 
```

**Note**: if you give it a valid `$comment` instance it will return it back to you.

You can also use various getter methods to retrieve comments:

Get Post Comments

```php
Comment::of($post)->get();
```

Get User Comments

```php
Comment::by($owner)->get();
```

Get Post Comments by User

```php
Comment::of($post)->by($owner)->get();
```

Eager Load Comment Replies and Replies Count

```php
Comment::of($post)->by($owner)->withReplies()->withRepliesCount()->get();
```

Get Only Accepted Replies

```php
Comment::of($post)->withAcceptedReplies()->get();

Comment::of($post)->withAcceptedRepliesCount()->get();
```

Get Only Rejected Replies

```php
Comment::of($post)->withRejectedReplies()->get();

Comment::of($post)->withRejectedRepliesCount()->get();
```

These methods can also accept callback functions for further customization.

You can use callback function within the `with`, `withCount`, `load` and `loadCount` methods:

```php
Comment::of($post)->withReplies(function ($query) {

    $query->where('accepted', true);

})->get();

// Or

Comment::of($post)->withAcceptedReplies(function ($query) {

    $query->whereNotNull('rate');

})->get();
```

You can also use `with` and `load` methods from commentable and owner models like this:

```php
Post::withComments()->get();

Post::withAcceptedComments()->get();

User::withComments()->get();

User::withAcceptedComments()->get();

$post->loadAcceptedComments();

$owner->loadAcceptedComments();
```

These methods accept `callback` function as well.

You can also load `replies`/`repliesCount`.

```php
$comment->loadReplies(); 

$comment->loadRepliesCount(); 

$comment->loadAcceptedReplies(); 

$comment->loadAcceptedRepliesCount(); 

$comment->loadRejectedReplies(); 

$comment->loadRejectedRepliesCount(); 
```

And all of them accept a callback function:

```php
$comment->loadReplies(function ($query) {
    $query->where('accepted', true);
}); 
```

### Relation Methods

`Comment` Model has this relation setup that you may need to use in your app:

```php
// To get the commentable model of a comment
$comment->commentable; 

// To get the owner model of a comment
$comment->owner; 

// To get the comment parent if it's a reply
$comment->parent; 

// To get the comment replies if it's a parent comment
$comment->replies;

// Or maybe add some conditions 

$comment->replies()->onlyAccepted()->get();
```

You can get the `comments` from `related` model like this:

```php
$post->comments();

$post->replies();

$owner->comments();

$owner->replies();
```

Every related model `CanComment` or `HasComments` have these built-in methods as well:

```php
$post->morphsArray();

$owner->morphsArray();

$post->primaryId();

$owner->primaryId();
```

You can use the `morphsArray()` method to filter by `commentable` or `owner` like this:

```php
Comment::where($post->morphsArray())->get();

// which is similr to this
Comment::of($post);

// or
Comment::where($owner->morphsArray())->get();

// which is similr to this
Comment::by($owner);

// or
$post->commentsBy($owner);

$owner->commentsOn($owner);
```

But the difference is that (`of`, `by`) methods return `onlyParent` comments `withRepliesCount`.

While this code:

```php
Comment::where($owner->morphsArray())->get();
```

**Returns** `comments` and `replies` toghter **without** reply count and you will have to check the `parent_id` to know which is the `parent` comment and which is the `reply`.

### Filters

If you want to filter the comments you can use these methods:

```php
// Available filters:
Comment::of($post)->onlyParent()->get();

Comment::of($post)->onlyAccepted()->get();

Comment::of($post)->onlyRejected()->get();

Comment::of($post)->onlyRated()->get();

Comment::of($post)->onlyNotRated()->get();
```

You can also chain filters like this:

```php
Comment::of($post)
    ->by($owner)
    ->onlyParents()
    ->onlyAccepted()
    ->onlyRated()
    ->get();
```

### Checkers

You can Check if `$comment` has `replies` or `parent`

```php
$comment->hasReplies();

$comment->hasParent();
```

You can also check if a `commentable` model `hasCommentsBy` an `owner` or the other way around:

```php
$post->hasCommentsBy($owner);

$owner->hasCommentsOn($post);
```

Check if a `$comment` belongs to a related `Model`:

```php
$post->hasComment($comment);

$owner->commented($comment);
```

## Commentable Table

The structure of the coments table is as follows:

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->morphs('commentable');
    $table->morphs('owner');

    $table->text('body');
    $table->boolean('accepted')->default(true);
    $table->double('rate', 15, 8)->nullable();
    $table->bigInteger('parent_id');

    $table->timestamps();
});
```

## License

This package is released under the MIT license (MIT).