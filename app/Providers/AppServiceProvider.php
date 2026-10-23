<?php

namespace App\Providers;

use App\Listeners\MergeGuestCart;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use App\Services\AdminNotifier;
use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, MergeGuestCart::class);

        User::created(fn (User $user) => AdminNotifier::newCustomer($user));

        View::composer(['frontend.*', 'admin.*'], function ($view) {
            $view->with('setting', fn(string $key, mixed $default = null) => Setting::get($key, $default));
        });

        View::composer('frontend.*', function ($view) {
            $view->with('menuCategories', Category::menuTree());
            $view->with('cartSummary', app(CartService::class)->summary());
            $view->with('wishlistIds', auth()->check() ? auth()->user()->wishlistProductIds() : []);
        });
    }
}
