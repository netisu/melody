<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    Admin\AdminController,
};
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;


///Route::domain(env('ADMIN_DOMAIN', 'admin.netisu.com'))->group(function () {
    Route::get('/', [AdminController::class, 'AdminIndex'])->name('page');
    Route::group(['as' => 'users.', 'prefix' => 'users'], function () {
        Route::get('/', [AdminController::class, 'UserIndex'])->name('page');
        Route::get('/manage/{id}', [AdminController::class, 'ManageUser'])->name('manage-user');
        Route::get('/gift/{id}', [AdminController::class, 'GiftUser'])->name('gift-user');
    });
    Route::group(['as' => 'items.', 'prefix' => 'items'], function () {
        Route::get('/', [AdminController::class, 'ItemIndex'])->name('page');
        Route::get('/manage/{id}', [AdminController::class, 'ManageItem'])->name('manage-item');
        Route::group(['as' => 'create.', 'prefix' => 'create'], function () {
            Route::get('/', [AdminController::class, 'CreateIndex'])->name('create-item');
            Route::post('/validate', [AdminController::class, 'uploadItem'])->name('validate');
        });
    });
    Route::group(['as' => 'messaging.', 'prefix' => 'intranet'], function () {
        Route::get('/', [AdminController::class, 'messagingIndex'])->name('page');
        Route::post('/validate', [AdminController::class, 'postMessage'])->name('validate');
    });
    Route::group(['as' => 'reports.', 'prefix' => 'reports'], function () {
        Route::get('/', [AdminController::class, 'ReportIndex'])->name('page');
        Route::get('/view/{id}', [AdminController::class, 'ReportIndex'])->name('view');
    });
    Route::group(['as' => 'assets.', 'prefix' => 'assets'], function () {
        Route::get('/approve', [AdminController::class, 'ContentApprovingIndex'])->name('approve');
    });
//});
