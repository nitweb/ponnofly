<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\VerifyEmailController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FlashDealController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PrivacyPageController;
use App\Http\Controllers\Admin\TermsPageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');

        Route::post('register', [RegisteredUserController::class, 'store']);

        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

Route::middleware('auth:admin')
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Laravel Default Routes
        Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Custom Routes
        Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
        // Banner Routes
        Route::resource('banners', BannerController::class)->except('show');
        // Category Routes
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('categories', CategoryController::class)->except('show');

        Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::resource('products', ProductController::class)->except('show');

        Route::patch('brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');
        Route::resource('brands', BrandController::class)->except('show');
        // Flash Deal Routes
        Route::patch('flash-deals/{flash_deal}/toggle', [FlashDealController::class, 'toggle'])->name('flash-deals.toggle');
        Route::resource('flash-deals', FlashDealController::class)->except('show');
        // Coupon Routes
        Route::patch('coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::resource('coupons', CouponController::class)->except('show');
        // About Page Routes
        Route::get('about', [AboutController::class, 'edit'])->name('about.edit');
        Route::put('about', [AboutController::class, 'update'])->name('about.update');
        // Privacy / Terms Page Routes
        Route::get('privacy', [PrivacyPageController::class, 'edit'])->name('privacy.edit');
        Route::put('privacy', [PrivacyPageController::class, 'update'])->name('privacy.update');
        Route::get('terms', [TermsPageController::class, 'edit'])->name('terms.edit');
        Route::put('terms', [TermsPageController::class, 'update'])->name('terms.update');
        // FAQ Routes
        Route::patch('faqs/reorder', [FaqController::class, 'reorder'])->name('faqs.reorder');
        Route::patch('faqs/{faq}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle');
        Route::resource('faqs', FaqController::class)->only(['index', 'store', 'update', 'destroy']);
        // Message Routes
        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::patch('messages/read-all', [MessageController::class, 'readAll'])->name('messages.read-all');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
        // Customer Routes
        Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        // Order Routes
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
        Route::patch('orders/{order}/note', [OrderController::class, 'note'])->name('orders.note');
        // Setting Routes
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
