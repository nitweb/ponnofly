<?php

use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\AccountOrderController;
use App\Http\Controllers\Frontend\AddressController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\FaqController;
use App\Http\Controllers\Frontend\FlashDealController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController as StorefrontProductController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\ReturnsController;
use App\Http\Controllers\Frontend\ShippingController;
use App\Http\Controllers\Frontend\ShopController;
use App\Http\Controllers\Frontend\PrivacyController;
use App\Http\Controllers\Frontend\TermsController;
use App\Http\Controllers\Frontend\TrackController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:120,1')->name('search.suggest');
Route::get('/flash-deals', [FlashDealController::class, 'index'])->name('flash-deals');
Route::get('/category/{category:slug}', [ShopController::class, 'category'])->name('category.show');
Route::get('/brand/{brand:slug}', [ShopController::class, 'brand'])->name('brand.show');
Route::get('/product/{product:slug}', [StorefrontProductController::class, 'show'])->name('product.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::prefix('cart')->as('cart.')->middleware('throttle:120,1')->group(function () {
    Route::get('summary', [CartController::class, 'summary'])->name('summary');
    Route::post('items', [CartController::class, 'store'])->name('items.store');
    Route::patch('items/{key}', [CartController::class, 'update'])->where('key', '\d+-\d+')->name('items.update');
    Route::delete('items/{key}', [CartController::class, 'destroy'])->where('key', '\d+-\d+')->name('items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::post('coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::put('zone', [CartController::class, 'zone'])->name('zone');
});

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/order/success/{number}', [CheckoutController::class, 'success'])->where('number', 'NM-\d+')->name('checkout.success');
Route::get('/order/invoice/{number}', [CheckoutController::class, 'invoice'])->where('number', 'NM-\d+')->name('checkout.invoice');

Route::get('/about', [AboutController::class, 'show'])->name('about.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');
Route::get('/privacy', [PrivacyController::class, 'show'])->name('privacy.show');
Route::get('/returns', [ReturnsController::class, 'show'])->name('returns.show');
Route::get('/shipping', [ShippingController::class, 'show'])->name('shipping.show');
Route::get('/terms', [TermsController::class, 'show'])->name('terms.show');

Route::get('/track', [TrackController::class, 'show'])->name('track.show');
Route::post('/track', [TrackController::class, 'lookup'])->name('track.lookup');

Route::get('/dashboard', fn () => to_route('account.index'))
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->prefix('account')->as('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');

    Route::get('orders', [AccountOrderController::class, 'index'])->name('orders');
    Route::get('orders/{number}', [AccountOrderController::class, 'show'])->where('number', 'NM-\d+')->name('orders.show');
    Route::post('orders/{number}/reorder', [AccountOrderController::class, 'reorder'])->where('number', 'NM-\d+')->name('orders.reorder');

    Route::get('profile', [AccountController::class, 'profile'])->name('profile');
    Route::patch('profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('password', [AccountController::class, 'updatePassword'])->name('password.update');

    Route::get('addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::patch('addresses/{address}/default', [AddressController::class, 'default'])->name('addresses.default');
    Route::delete('addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('wishlist/toggle', [WishlistController::class, 'toggle'])->middleware('throttle:120,1')->name('wishlist.toggle');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
