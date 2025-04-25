<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    Chat\ChatController,
    Endpoints\RssController,
    Endpoints\UserController,
    Endpoints\AdminController,
    Endpoints\AvatarController,
    Endpoints\RenderController,
    Endpoints\ItemApiController,
    Endpoints\ThumbnailController,
    Endpoints\SearchSiteController,
    Endpoints\NotificationController,
    Endpoints\SpacesController,
    Endpoints\SettingsController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['as' => 'api.'], function () {
    Route::get('/', function () {
        return redirect()->to(config('Values.production.domains.main'));
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    Route::group(['as' => 'messaging.', 'prefix' => 'messaging'], function () {
        Route::post("/send-message/to/{id}", [ChatController::class, "sendMessage"]);
        Route::get("/fetch-messages/to/{id}", [ChatController::class, "fetchMessages"])->name('fetchMessages');
    });

    Route::group(['as' => 'spaces.', 'prefix' => 'spaces'], function () {
        Route::get("/get-members/{spaceId}/{rankId}", [SpacesController::class, "getSpaceMembersByRank"]);
    });


    Route::get('/search', [SearchSiteController::class, 'all'])->name('search');

    Route::get('/render/validate/{id}', [RenderController::class, 'userRender'])->name('avatar');

    Route::group(['as' => 'user.', 'prefix' => 'users'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        Route::get('/name-availability', [UserController::class, 'UsernameAvailability'])->name('name-availability');
        Route::get('/validate-email', [UserController::class, 'ValidateEmail'])->name('email-validate');
        Route::get('/validate-password', [UserController::class, 'ValidatePassword'])->name('password-validate');
        Route::get('/profile-data/{username}', [UserController::class, 'getUser'])->name('profile-data');
        Route::get('/online/{id}', [UserController::class, 'getStatus'])->name('online');
        Route::get('/inventory/{id}', [UserController::class, 'getUserItems'])->name('inventory');
        Route::get('/inventory/currently-wearing/{id}', [UserController::class, 'getUserCurrentlyWearing'])->name('currently-wearing');

        Route::get('/status-list', [UserController::class, 'getUserStatus'])->name('status');
        Route::get('/user/img/{id}', [UserController::class, 'getAvatar'])->name('avatar');
        Route::get('/follow/{user}', [UserController::class, 'follow'])->name('follow');
        Route::post('/unfollow/{user}', [UserController::class, 'unfollow'])->name('unfollow');
    });

    Route::group(['as' => 'dashboard.', 'prefix' => 'dash'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        Route::get('/status-list', [UserController::class, 'getDashboardStatus'])->name('statuses');
    });

    Route::group(['as' => 'notif.', 'prefix' => 'notifications'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        Route::post('/read-all', [NotificationController::class, 'ReadAll'])->name('read-all');
    });
    Route::group(['as' => 'item.', 'prefix' => 'item'], function () {
        Route::post('/rerender/{id}', [ItemApiController::class, 'reRenderItem'])->name('rerender');
    });
    Route::group(['as' => 'store.', 'prefix' => 'market'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        Route::get('/items/{category}', [ItemApiController::class, 'getItemsByCategory'])->name('items');

        Route::get('/items/event/{eventId}', [ItemApiController::class, 'getEventItems'])->name('event.items');
        Route::post('/item/purchase/{id}/{currencyType}', [ItemApiController::class, 'purchase'])->name('purchase');
    });

    Route::group(['as' => 'avatar.', 'prefix' => 'avatar'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        Route::get('/{category}', [AvatarController::class, 'getItemsByCategory'])->name('items');
        Route::get('/wearing/hats', [AvatarController::class, 'getWearingHats'])->name('wearing-hats');

        Route::get('/wear/{id}/{slot}', [AvatarController::class, 'WearItem'])->name('wear-item');
        Route::get('/take-off/{id}/{slot}', [AvatarController::class, 'RemoveItem'])->name('remove-item');

    });

    Route::group(['as' => 'settings.', 'prefix' => 'settings'], function () {
        Route::get('/', function () {
            return redirect()->to(config('Values.production.domains.main'));
        });
        
        Route::post('/profile-picture/upload', [SettingsController::class, 'uploadProfilePicture'])->name('uploadProfilePicture');
        Route::post('/change-country/{country}', [SettingsController::class, 'changeCountry'])->name('changeCountry');
        Route::post('/change-password', [SettingsController::class, 'changePassword'])->name('changePassword');

    });

    Route::get('/rss-feed', [RssController::class, 'index'])->name('rss');
    Route::get('/thumbnails/{type}/{id}', [ThumbnailController::class, 'getThumbnail'])->name('thumbnails');

    Route::get('/', function () {
        return redirect()->to(config('Values.production.domains.main'));
    });

    Route::group(['as' => 'admin.', 'prefix' => config('Values.version')], function () {
        Route::post('/enable-maintenance', [AdminController::class, 'enableMaintenance'])->name('enableMaintenance');
        Route::post('/disable-maintenance', [AdminController::class, 'disableMaintenance'])->name('disableMaintenance');
        Route::post('/gift-item/{itemID}/{userID}', [AdminController::class, 'giftItem'])->name('gift-item');
        Route::post('/purge/{type}/{assetId}', [AdminController::class, 'purge'])->name('purge');
    });
});
