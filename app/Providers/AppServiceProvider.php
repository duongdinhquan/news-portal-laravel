<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Category;
use Illuminate\Support\Facades\View;
use App\Models\Media;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use App\Policies\MediaPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Domain services are container-autowired.
    }

    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        View::composer('layouts.public', function ($view): void {
            $view->with('navCategories', Category::where('is_active', true)->orderBy('sort_order')->get());
        });
    }
}
