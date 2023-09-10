<?php

namespace Melsaka\Commentable;

use Illuminate\Support\ServiceProvider;

class CommentableServiceProvider extends ServiceProvider
{
    // package migrations
    private $migration = __DIR__ . '/database/migrations/';

    private $config = __DIR__ . '/config/commentable.php';


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->config, 'commentable');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom([ $this->migration ]);

        $this->publishes([ $this->config => config_path('commentable.php') ], 'commentable');
    }
}
