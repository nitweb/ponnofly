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
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FlashDealController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PrivacyPageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnRequestController;
use App\Http\Controllers\Admin\ReturnsPageController;
use App\Http\Controllers\Admin\ShippingPageController;
use App\Http\Controllers\Admin\TermsPageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffController;
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

        // Notification Routes (every signed-in admin)
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::patch('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');

        // Profile Routes (every signed-in admin)
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('profile/activity', [ProfileController::class, 'clearActivity'])->name('profile.activity.clear');

        // Banner Routes
        Route::middleware('permission:banners')->group(function () {
            Route::resource('banners', BannerController::class)->except('show');
        });
        // Category Routes
        Route::middleware('permission:categories')->group(function () {
            Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
            Route::resource('categories', CategoryController::class)->except('show');
        });
        // Product Routes
        Route::middleware('permission:products')->group(function () {
            Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
            Route::resource('products', ProductController::class)->except('show');
        });
        // Brand Routes
        Route::middleware('permission:brands')->group(function () {
            Route::patch('brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('brands.toggle');
            Route::resource('brands', BrandController::class)->except('show');
        });
        // Flash Deal Routes
        Route::middleware('permission:flash')->group(function () {
            Route::patch('flash-deals/{flash_deal}/toggle', [FlashDealController::class, 'toggle'])->name('flash-deals.toggle');
            Route::resource('flash-deals', FlashDealController::class)->except('show');
        });
        // Coupon Routes
        Route::middleware('permission:coupons')->group(function () {
            Route::patch('coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
            Route::resource('coupons', CouponController::class)->except('show');
        });
        // About / Privacy / Terms / Shipping / Returns Page Routes
        Route::middleware('permission:pages,singleton')->group(function () {
            Route::get('about', [AboutController::class, 'edit'])->name('about.edit');
            Route::put('about', [AboutController::class, 'update'])->name('about.update');
            Route::get('privacy', [PrivacyPageController::class, 'edit'])->name('privacy.edit');
            Route::put('privacy', [PrivacyPageController::class, 'update'])->name('privacy.update');
            Route::get('terms', [TermsPageController::class, 'edit'])->name('terms.edit');
            Route::put('terms', [TermsPageController::class, 'update'])->name('terms.update');
            Route::get('shipping', [ShippingPageController::class, 'edit'])->name('shipping.edit');
            Route::put('shipping', [ShippingPageController::class, 'update'])->name('shipping.update');
            Route::get('returns', [ReturnsPageController::class, 'edit'])->name('returns.edit');
            Route::put('returns', [ReturnsPageController::class, 'update'])->name('returns.update');
        });
        // FAQ Routes
        Route::middleware('permission:faqs')->group(function () {
            Route::patch('faqs/reorder', [FaqController::class, 'reorder'])->name('faqs.reorder');
            Route::patch('faqs/{faq}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle');
            Route::resource('faqs', FaqController::class)->only(['index', 'store', 'update', 'destroy']);
        });
        // Message Routes
        Route::middleware('permission:messages')->group(function () {
            Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
            Route::patch('messages/read-all', [MessageController::class, 'readAll'])->name('messages.read-all');
            Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
            Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
        });
        // Customer Routes
        Route::middleware('permission:customers')->group(function () {
            Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
            Route::get('customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
            Route::patch('customers/{customer}/restriction', [\App\Http\Controllers\Admin\CustomerController::class, 'restriction'])->name('customers.restriction');
        });
        // Order Routes
        Route::middleware('permission:orders')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
            Route::get('orders/{order}/invoice/pdf', [OrderController::class, 'invoicePdf'])->name('orders.invoice.pdf');
            Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::patch('orders/{order}/note', [OrderController::class, 'note'])->name('orders.note');
            Route::get('return-requests', [ReturnRequestController::class, 'index'])->name('return-requests.index');
            Route::get('return-requests/search-orders', [ReturnRequestController::class, 'searchOrders'])->name('return-requests.search-orders');
            Route::post('return-requests', [ReturnRequestController::class, 'store'])->name('return-requests.store');
            Route::get('return-requests/{orderReturn}', [ReturnRequestController::class, 'show'])->name('return-requests.show');
            Route::patch('return-requests/{orderReturn}/approve', [ReturnRequestController::class, 'approve'])->name('return-requests.approve');
            Route::patch('return-requests/{orderReturn}/reject', [ReturnRequestController::class, 'reject'])->name('return-requests.reject');
        });
        // Report Routes
        Route::middleware('permission:reports')->prefix('reports')->as('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('profit-loss', [ReportController::class, 'profit'])->name('profit');
            Route::get('products', [ReportController::class, 'products'])->name('products');
            Route::get('customers', [ReportController::class, 'customers'])->name('customers');
            Route::get('staff', [ReportController::class, 'staff'])->name('staff');
            Route::get('staff/{admin}', [ReportController::class, 'staffShow'])->name('staff.show');
            Route::get('export/{report}', [ReportController::class, 'export'])->name('export');
        });
        // Expense Routes
        Route::middleware('permission:expenses')->group(function () {
            Route::get('expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
            Route::resource('expense-categories', ExpenseCategoryController::class)->only(['index', 'store', 'update', 'destroy'])->parameters(['expense-categories' => 'category'])->names('expense-categories');
            Route::resource('expenses', ExpenseController::class)->except('show');
        });
        // Role Routes
        Route::middleware('permission:roles')->group(function () {
            Route::resource('roles', RoleController::class)->except('show');
        });
        // Staff Routes
        Route::middleware('permission:staff')->group(function () {
            Route::patch('staff/{staff}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
            Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'staff']);
        });
        // Setting Routes
        Route::middleware('permission:settings,singleton')->group(function () {
            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
