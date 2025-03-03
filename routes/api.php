<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Api\V1\DataController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\ApiDocController;
use App\Http\Controllers\Api\V1\RateLimitController;
use App\Http\Controllers\Api\V1\NotificationTemplateController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\FileCollectionController;
use App\Http\Controllers\Api\V1\CacheController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Facades\Health;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Public cached routes
    Route::middleware(['cache.tags:public'])->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('categories', [CategoryController::class, 'index']);
    });
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);

    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('add', [CartController::class, 'add']);
        Route::put('{productId}', [CartController::class, 'update']);
        Route::delete('{productId}', [CartController::class, 'remove']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('logout', [AuthController::class, 'logout']);
        
        // Protected cached routes
        Route::middleware(['auth:sanctum', 'cache.tags:private'])->group(function () {
            Route::get('orders', [OrderController::class, 'index']);
            Route::get('profile', [UserController::class, 'profile']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::post('/', [OrderController::class, 'store']);
            Route::get('{order}', [OrderController::class, 'show']);
            Route::patch('{order}/status', [OrderController::class, 'updateStatus']);
        });

        // Reviews
        Route::prefix('reviews')->group(function () {
            Route::get('/', [ReviewController::class, 'index']);
            Route::post('/', [ReviewController::class, 'store']);
            Route::put('{review}', [ReviewController::class, 'update']);
            Route::delete('{review}', [ReviewController::class, 'destroy']);
            Route::patch('{review}/approve', [ReviewController::class, 'approve']);
        });

        // Admin routes
        Route::middleware('admin')->group(function () {
            // Products management
            Route::prefix('products')->group(function () {
                Route::post('/', [ProductController::class, 'store']);
                Route::put('{product}', [ProductController::class, 'update']);
                Route::delete('{product}', [ProductController::class, 'destroy']);
            });

            // Categories management
            Route::prefix('categories')->group(function () {
                Route::post('/', [CategoryController::class, 'store']);
                Route::put('{category}', [CategoryController::class, 'update']);
                Route::delete('{category}', [CategoryController::class, 'destroy']);
                Route::post('reorder', [CategoryController::class, 'reorder']);
            });
        });

        Route::prefix('activity-logs')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index']);
            Route::get('/user', [ActivityLogController::class, 'userActivity']);
            Route::get('/model', [ActivityLogController::class, 'modelHistory']);
        });

        // Data management routes
        Route::prefix('data')->middleware('admin')->group(function () {
            Route::post('export', [DataController::class, 'export']);
            Route::post('import', [DataController::class, 'import']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/{notification}', [NotificationController::class, 'show']);
            Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
            Route::post('/mark-as-unread', [NotificationController::class, 'markAsUnread']);
            Route::delete('/{notification}', [NotificationController::class, 'destroy']);
        });

        Route::prefix('notification-templates')->middleware('admin')->group(function () {
            Route::get('/', [NotificationTemplateController::class, 'index']);
            Route::post('/', [NotificationTemplateController::class, 'store']);
            Route::get('/{template}', [NotificationTemplateController::class, 'show']);
            Route::put('/{template}', [NotificationTemplateController::class, 'update']);
            Route::delete('/{template}', [NotificationTemplateController::class, 'destroy']);
        });

        Route::prefix('search')->group(function () {
            Route::get('/', [SearchController::class, 'search']);
            Route::get('/suggest', [SearchController::class, 'suggest']);
            Route::post('/reindex', [SearchController::class, 'reindex'])->middleware('admin');
        });

        // File routes
        Route::prefix('files')->group(function () {
            Route::get('/', [FileController::class, 'index']);
            Route::post('/', [FileController::class, 'store'])->middleware('admin');
            Route::get('/{file}', [FileController::class, 'show']);
            Route::put('/{file}', [FileController::class, 'update'])->middleware('admin');
            Route::delete('/{file}', [FileController::class, 'destroy'])->middleware('admin');
            Route::get('/{file}/download', [FileController::class, 'download']);
            Route::post('/{file}/duplicate', [FileController::class, 'duplicate'])->middleware('admin');
            Route::post('/{file}/move', [FileController::class, 'move'])->middleware('admin');
        });

        // File Collection routes
        Route::prefix('file-collections')->group(function () {
            Route::get('/', [FileCollectionController::class, 'index']);
            Route::post('/', [FileCollectionController::class, 'store'])->middleware('admin');
            Route::get('/{collection}', [FileCollectionController::class, 'show']);
            Route::put('/{collection}', [FileCollectionController::class, 'update'])->middleware('admin');
            Route::delete('/{collection}', [FileCollectionController::class, 'destroy'])->middleware('admin');
            Route::post('/{collection}/files', [FileCollectionController::class, 'addFiles'])->middleware('admin');
            Route::delete('/{collection}/files', [FileCollectionController::class, 'removeFiles'])->middleware('admin');
        });

        Route::prefix('folders')->group(function () {
            Route::get('/', [FolderController::class, 'index']);
            Route::post('/', [FolderController::class, 'store']);
            Route::get('/{folder}', [FolderController::class, 'show']);
            Route::post('/{folder}/move', [FolderController::class, 'move']);
            Route::delete('/{folder}', [FolderController::class, 'destroy']);
        });

        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('/{task}', [TaskController::class, 'show']);
            Route::put('/{task}', [TaskController::class, 'update']);
            Route::delete('/{task}', [TaskController::class, 'destroy']);
            Route::post('/{task}/execute', [TaskController::class, 'execute']);
            Route::get('/{task}/logs', [TaskController::class, 'logs']);
        });
    });

    // Webhook routes
    Route::post('webhook/payment', [WebhookController::class, 'handlePayment'])
        ->name('webhook.payment');

    // Public documentation routes
    Route::get('docs/latest', [ApiDocController::class, 'latest']);
    Route::get('docs/{apiDoc}', [ApiDocController::class, 'show']);

    // Protected documentation routes
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::prefix('docs')->group(function () {
            Route::get('/', [ApiDocController::class, 'index']);
            Route::post('/generate', [ApiDocController::class, 'generate']);
            Route::delete('/{apiDoc}', [ApiDocController::class, 'destroy']);
        });
    });
});

Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Reporting routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::post('/', [ReportController::class, 'store']);
        Route::get('/{report}', [ReportController::class, 'show']);
        Route::post('/{report}/generate', [ReportController::class, 'generate']);
        Route::get('/{report}/download', [ReportController::class, 'download']);
    });

    Route::get('monitoring/dashboard', [MonitoringController::class, 'dashboard']);
    Route::get('health', function() {
        return Health::check();
    });

    Route::prefix('backups')->group(function () {
        Route::get('/', [BackupController::class, 'index']);
        Route::post('/', [BackupController::class, 'store']);
        Route::get('/{backup}', [BackupController::class, 'show']);
        Route::get('/{backup}/download', [BackupController::class, 'download']);
        Route::post('/{backup}/restore', [BackupController::class, 'restore']);
        Route::delete('/{backup}', [BackupController::class, 'destroy']);
    });

    Route::prefix('webhooks')->group(function () {
        Route::get('/', [WebhookController::class, 'index']);
        Route::post('/', [WebhookController::class, 'store']);
        Route::get('/{webhook}', [WebhookController::class, 'show']);
        Route::put('/{webhook}', [WebhookController::class, 'update']);
        Route::delete('/{webhook}', [WebhookController::class, 'destroy']);
        Route::post('/{webhook}/regenerate-secret', [WebhookController::class, 'regenerateSecret']);
        Route::get('/{webhook}/deliveries', [WebhookController::class, 'deliveries']);
    });

    Route::prefix('rate-limits')->group(function () {
        Route::get('/', [RateLimitController::class, 'index']);
        Route::post('/', [RateLimitController::class, 'store']);
        Route::get('/{rateLimit}', [RateLimitController::class, 'show']);
        Route::put('/{rateLimit}', [RateLimitController::class, 'update']);
        Route::delete('/{rateLimit}', [RateLimitController::class, 'destroy']);
        Route::get('/{rateLimit}/logs', [RateLimitController::class, 'logs']);
    });

    Route::prefix('exports')->group(function () {
        Route::get('/', [ExportController::class, 'index']);
        Route::post('/', [ExportController::class, 'store']);
        Route::get('/{export}', [ExportController::class, 'show']);
        Route::get('/{export}/download', [ExportController::class, 'download']);
        Route::delete('/{export}', [ExportController::class, 'destroy']);
    });

    Route::prefix('imports')->group(function () {
        Route::get('/', [ImportController::class, 'index']);
        Route::post('/', [ImportController::class, 'store']);
        Route::get('/{import}', [ImportController::class, 'show']);
        Route::get('/{import}/failures', [ImportController::class, 'failures']);
        Route::delete('/{import}', [ImportController::class, 'destroy']);
    });

    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/stats', [AuditLogController::class, 'stats']);
        Route::get('/user-activity', [AuditLogController::class, 'userActivity']);
        Route::get('/model-history', [AuditLogController::class, 'modelHistory']);
        Route::get('/{log}', [AuditLogController::class, 'show']);
    });

    Route::prefix('cache')->group(function () {
        Route::get('/', [CacheController::class, 'index']);
        Route::post('/', [CacheController::class, 'store']);
        Route::get('/stats', [CacheController::class, 'stats']);
        Route::post('/flush', [CacheController::class, 'flush']);
        Route::post('/cleanup', [CacheController::class, 'cleanup']);
        Route::get('/{key}', [CacheController::class, 'show']);
        Route::delete('/{key}', [CacheController::class, 'destroy']);
    });
});

// V2 routes (for future use)
Route::prefix('v2')->group(function () {
    // V2 routes will go here
});

Route::prefix('auth')->group(function () {
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::post('register', [ApiAuthController::class, 'register']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [ApiAuthController::class, 'logout']);
        Route::get('user', [ApiAuthController::class, 'user']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Protected API routes here
}); 