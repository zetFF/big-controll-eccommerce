<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\ProductBundleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('admin.analytics');
    
    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/run', [TaskController::class, 'run'])->name('admin.tasks.run');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    
    // Settings Management
    Route::get('settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::get('settings/export', [SettingController::class, 'export'])->name('admin.settings.export');
    Route::post('settings/import', [SettingController::class, 'import'])->name('admin.settings.import');

    // Backup Management
    Route::resource('backups', BackupController::class);
    Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('admin.backups.download');
    Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('admin.backups.restore');

    // Activity Log
    Route::get('activities', [ActivityController::class, 'index'])->name('admin.activities.index');
    Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('admin.activities.show');
    Route::post('activities/clear', [ActivityController::class, 'clear'])->name('admin.activities.clear');

    // Health Monitoring
    Route::get('health', [HealthController::class, 'index'])->name('admin.health.index');

    // Error Logs
    Route::resource('error-logs', ErrorLogController::class)->except(['create', 'store', 'edit', 'update']);
    Route::post('error-logs/cleanup', [ErrorLogController::class, 'cleanup'])->name('admin.error-logs.cleanup');

    // Product Variants
    Route::get('products/{product}/variants/create', [ProductVariantController::class, 'create'])
        ->name('products.variants.create');
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])
        ->name('products.variants.store');
    Route::get('products/{product}/variants/{variant}/edit', [ProductVariantController::class, 'edit'])
        ->name('products.variants.edit');
    Route::put('products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])
        ->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])
        ->name('products.variants.destroy');

    // Product Bundles
    Route::resource('bundles', ProductBundleController::class);
});

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');

// Notifications
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::middleware('auth')->group(function () {
    Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
    Route::post('/compare/{product}', [CompareController::class, 'add'])->name('compare.add');
    Route::post('/compare/{product}/remove', [CompareController::class, 'remove'])->name('compare.remove');
    Route::post('/compare/clear', [CompareController::class, 'clear'])->name('compare.clear');
});

// Product Routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{type}/{id}', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');

// Auth Required Routes
Route::middleware('auth')->group(function () {
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');

    // Reviews
    Route::post('/reviews/{product}', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Compare
    Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
    Route::post('/compare/{product}', [CompareController::class, 'add'])->name('compare.add');
    Route::delete('/compare/{product}', [CompareController::class, 'remove'])->name('compare.remove');
    Route::post('/compare/clear', [CompareController::class, 'clear'])->name('compare.clear');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
        ->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
        ->name('password.email');
    
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
        ->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])
        ->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Include Laravel Breeze auth routes
require __DIR__.'/auth.php';

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    // ... other admin routes ...
});
