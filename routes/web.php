<?php

use App\Http\Controllers\{
    USController,
    Money\MoneyController,
    AuthController,
    Admin\AdminController,
    Chat\ChatController,
    Users\UserController,
    Market\MarketController,
    Spaces\SpaceController,
    HomeController,
    GrabController,
    Web3LoginController,
    MaintenanceController,
    GoogleSocialiteController,
    DiscordSocialiteController,
    AchievementController,
    Forum\ForumController,
    Leaderboard\LeaderboardController,
    Promocodes\PromocodeController,
    Develop\DevelopController,
    Notification,
    MembershipController,
    SearchController,
    PolicyController,
};
use App\Http\Middleware\{
    EnsurePasswordIsConfirmed,
    Admin
};

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Jobs\SendVerificationEmail;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Support\Facades\Queue;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" Middleware group. Now create something great!
|
*/

// Auto-login for local development (only if user 1 exists and not already logged in)
// This should only be uncommented for local testing and should be temporary.
/* if (app()->environment('local') && User::where('id', 1)->exists() && !Auth::check()) {
    Auth::loginUsingId(1);
}
*/
Route::domain(config('app.env') === 'production' ? config('Values.production.domains.main') : config('app.url'))
    ->middleware(['cacheable'])->group(function () {

        // --- Maintenance Routes ---
        Route::group(['as' => 'maintenance.', 'prefix' => 'maintenance'], function () {
            Route::get('/', [MaintenanceController::class, 'show'])->name('page');
            Route::post('/password', [MaintenanceController::class, 'authenticate'])->name('authenticate.password');
            Route::get('/exit', [MaintenanceController::class, 'Exit'])->name('exit');
        });

        // --- Leaderboard Routes ---
        Route::group(['as' => 'leaderboard.', 'prefix' => 'leaderboard'], function () {
            Route::get('/', [LeaderboardController::class, 'LeaderboardIndex'])->name('page');
        });

        // --- Authenticated User Routes (My Dashboard, Money) ---

        Route::group(['as' => 'my.', 'prefix' => 'my', 'middleware' => 'auth'], function () {
            Route::group(['as' => 'money.', 'prefix' => 'money'], function () {
                Route::get('/', [MoneyController::class, 'MoneyIndex'])->name('page');
                Route::post(uri: '/convert-currency', action: [MoneyController::class, 'convert'])->name(name: 'convert-currency');
            });
            Route::group(['as' => 'dashboard.', 'prefix' => 'dashboard'], function () {
                Route::get('/', [HomeController::class, 'DashboardIndex'])->name('page');
                Route::post('/', [HomeController::class, 'DashboardVal'])->name('validate');
            });
        });

        // --- User Profile & Settings Routes ---
        Route::group(['as' => 'user.'], function () {
            Route::get('/discover', [UserController::class, 'UserIndex'])->name('page');
            Route::get('/@{username}', [UserController::class, 'ProfileIndex'])->name('profile');
            Route::get('/@{username}/inventory', [UserController::class, 'InventoryIndex'])->name('inventory');
            Route::middleware(['auth', EnsurePasswordIsConfirmed::class])->group(function () {
                Route::group(['as' => 'settings.', 'prefix' => 'settings'], function () {
                    Route::get('/', [USController::class, 'edit'])->name('page');
                    Route::patch('/update', [USController::class, 'update'])->name('update');
                    Route::post('/delete-account', [USController::class, 'destroy'])->name('destroy');
                });
            });
        });

        // --- Promocodes Routes ---
        Route::group(['as' => 'promocodes.', 'prefix' => 'promocodes', 'middleware' => 'auth'], function () {
            Route::get('/', [PromocodeController::class, 'PromocodeIndex'])->name('page');
            Route::post('/validate', [PromocodeController::class, 'PromocodeVal'])->name('redeem');
        });

        // --- Avatar Customization Routes ---
        Route::group(['as' => 'avatar.', 'prefix' => 'customize', 'middleware' => 'auth'], function () {
            Route::get('/', [GrabController::class, 'customizeIndex'])->name('page');
            Route::post('/update', [GrabController::class, 'UpdateAvatar'])->name('update');
        });

        // --- Forum Routes ---
        Route::group(['as' => 'forum.', 'prefix' => 'discuss'], function () {
            // Redirect to the first forum page, 301 for permanent redirect
            Route::get('/', function () {
                return redirect()->route('forum.page', ['id' => 1], 301);
            });
            Route::get('/{id}', [ForumController::class, 'ForumIndex'])->name('page');
            Route::group(['as' => 'your.', 'prefix' => 'your'], function () {

                Route::get('/posts', [ForumController::class, 'myPosts'])->name('posts');
            });

            Route::get('/post/{id}/{slug}', [ForumController::class, 'ForumPost'])->name('post');
            Route::group(['as' => 'create.', 'prefix' => 'create', 'middleware' => 'verified'], function () {
                Route::get('/{id}', [ForumController::class, 'ForumCreate'])->name('page');
                Route::post('/{id}/validate', [ForumController::class, 'ForumVal'])->name('validate');
            });
            Route::group(['as' => 'reply.', 'prefix' => 'create/reply', 'middleware' => 'verified'], function () {
                Route::post('/{id}/validate', [ForumController::class, 'ForumReply'])->name('validate');
            });
        });

        // --- Chat/Messaging Routes ---
        Route::group(['as' => 'chat.', 'prefix' => 'messages'], function () {
            Route::get('/{userID}', [ChatController::class, 'ChatIndex'])
                ->name('messages');
            Route::post('/validate/{id}', [ChatController::class, 'sendMessage'])
                ->name('validate');
        });

        // --- Subscription Checkout ---
        Route::get('/subscription-checkout', function (Request $request) {
            // Ensure user is authenticated before attempting checkout
            if (!$request->user()) {
                return redirect()->route('auth.login.page')->with('error', 'Please login to subscribe.');
            }

            try {
                $checkout = $request->user()
                    ->newSubscription('default', 'price_basic_monthly')
                    ->trialDays(5)
                    ->allowPromotionCodes()
                    ->checkout([
                        'success_url' => route('purchase.success'),
                        'cancel_url' => route('purchase.cancelled'),
                    ]);
                return inertia()->location($checkout->url);
            } catch (\Exception $e) {
                \Log::error('Stripe checkout error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to initiate checkout. Please try again.');
            }
        })->middleware('auth')->name('subscription.checkout');

        Route::group(['as' => 'purchase.', 'prefix' => 'purchase'], function () {
            Route::get('/success', [MembershipController::class, 'success'])->name('success');
            Route::get('/cancelled', [MembershipController::class, 'cancelled'])->name('cancelled');
        });

        // --- Achievements Route ---
        Route::get('/achievements', [AchievementController::class, 'AchievementList'])->name('acheivements');

        // --- Notifications Routes ---
        Route::group(['as' => 'notification.', 'prefix' => 'notifications', 'middleware' => 'auth'], function () {
            Route::get('/', [Notification::class, 'AllNotifications'])->name('page');
            Route::get('/view/{id}', [Notification::class, 'Notification'])->name('view');
        });

        // --- Authentication Routes ---
        Route::group(['as' => 'auth.', 'prefix' => 'auth'], function () {

            Route::get('/logout', [AuthController::class, 'UserExit'])->name('logout');
            Route::get('/auth-providers', [AuthController::class, 'ProvidersIndex'])->name('providers');

            // Language setting route
            Route::get('/set-language/{language}', function ($language) {
                Session::put('locale', $language);
                return redirect()->back();
            })->name('language');


            Route::group(['middleware' => 'guest'], function () {
                // Guest-only authentication routes
                Route::group(['as' => 'login.', 'prefix' => 'login'], function () {
                    // Google login
                    Route::group(['as' => 'google.', 'prefix' => 'google'], function () {
                        Route::get('/v2', [GoogleSocialiteController::class, 'redirectToGoogle'])->name('auth');
                        Route::get('/callback', [GoogleSocialiteController::class, 'handleCallback'])->name('validation');
                    });

                    // Discord login
                    Route::group(['as' => 'discord.', 'prefix' => 'discord'], function () {
                        Route::get('/', [DiscordSocialiteController::class, 'redirectToDiscord'])->name('auth');
                        Route::get('/callback', [DiscordSocialiteController::class, 'handleCallback'])->name('validation');
                    });

                    //  remove (//) If you want Facebook login
                    //Route::get('auth/facebook', [FacebookSocialiteController::class, 'redirectToFB']);
                    //Route::get('callback/facebook', [FacebookSocialiteController::class, 'handleCallback']);
                    Route::get('/', [AuthController::class, 'LoginIndex'])->name('page');
                    Route::post('/validate', [AuthController::class, 'LoginVal'])->name('validate');
                    Route::get('/validate/metamask', [Web3LoginController::class, 'message'])->name('metamask');
                    Route::post('/validate/meta-mask-api', [Web3LoginController::class, 'verify'])->name('metamask.validation');
                });

                Route::group(['as' => 'register.', 'prefix' => 'get-started'], function () {

                    Route::get('/', [AuthController::class, 'RegisterIndex'])->name('page');
                    Route::post('/validate', [AuthController::class, 'RegisterVal'])->name('validate');
                });

                Route::group(['as' => 'forgot.', 'prefix' => 'forgot-password'], function () {
                    Route::get('/', [AuthController::class, 'ForgotIndex'])->name('page');

                    Route::get('/reset-password/{token}', function (string $token) {
                        return view('auth.reset-password', ['token' => $token]);
                    })->middleware('guest')->name('password.reset');

                    Route::post('/', function (Request $request) {
                        $request->validate(['email' => 'required|email']);
                        $status = Password::sendResetLink($request->only('email'));

                        return $status === Password::RESET_LINK_SENT
                            ? back()->with(['status' => __($status)])
                            : back()->withErrors(['email' => __($status)]);
                    })->name('password.email');
                });
            });
        });

        // --- Password Confirmation Routes ---
        Route::group(['as' => 'password.', 'prefix' => 'password'], function () {
            Route::get('/confirm-identity', [AuthController::class, 'showConfirmForm'])->name('confirm.form');
            Route::post('/confirm-password', [AuthController::class, 'confirmPassword'])->name('confirm');
        });

        // --- Welcome Route (Guest Only) ---
        Route::group(['as' => 'welcome.', 'middleware' => 'guest', 'prefix' => '/'], function () {
            Route::get('/', [HomeController::class, 'WelcomeIndex'])->name('page');
        });

        // --- Games Routes ---
        Route::group(['as' => 'games.', 'prefix' => 'games'], function () {
            Route::get('/', fn() => inertia('Games/Index'))->name('page'); // Shorter closure syntax
            Route::get('/1', fn() => inertia('Games/Game'))->name('game'); // Shorter closure syntax
        });

        // --- Account Deletion Route ---
        Route::get('/deletion', function () {
            return inertia('AccountDeleted');
        })->name('removed');

        // --- Store / Market Routes ---
        Route::group(['as' => 'store.', 'prefix' => 'market'], function () {
            Route::get('/', [MarketController::class, 'StoreIndex'])->name('page');
            Route::group(['as' => 'create.', 'prefix' => 'create', 'middleware' => 'auth'], function () {
                Route::get('/', [MarketController::class, 'CreateIndex'])->name('page');
                Route::post('/validate', [MarketController::class, 'uploadItem'])->name('validate');
            });
            Route::get('/item/{id}', [MarketController::class, 'StoreItem'])->name('item');
            Route::get('/item/{id}/edit', [MarketController::class, 'edit'])->name('edit');
            Route::post('/item/{id}/edit', [MarketController::class, 'update'])->name('edit.validate');

            Route::post('/item/purchase/{id}/{currencyType}', [MarketController::class, 'purchase'])->name('purchase');
        });

        // --- Membership/Upgrade Routes ---
        Route::group(['as' => 'membership.', 'prefix' => 'upgrade'], function () {
            Route::get('/', [MembershipController::class, 'MembershipIndex'])->name('page');
            Route::get('/purchase/{id}', [MembershipController::class, 'MembershipPurchase'])->name('purchase');
        });

        // --- Search Routes ---
        Route::group(['as' => 'search.', 'prefix' => 'search'], function () {
            Route::get('/', [SearchController::class, 'showResults'])->name('page');
        });

        // --- Develop Routes ---
        Route::group(['as' => 'develop.', 'prefix' => 'develop'], function () {
            Route::get('/', [DevelopController::class, 'DevelopIndex'])->name('page');
        });

        // --- Space Routes ---
        Route::group(['as' => 'spaces.', 'prefix' => 'spaces'], function () {
            Route::get('/', [SpaceController::class, 'SpacesIndex'])->name('page');
            Route::group(['as' => 'create.', 'prefix' => 'create', 'middleware' => ['auth', 'verified']], function () {
                Route::get('/', [SpaceController::class, 'SpaceCreate'])->name('page');
                Route::post('/create/validate', [SpaceController::class, 'SpaceVal'])->name('validate');
            });
            Route::post('/membership/{id}', [SpaceController::class, 'SpaceMembership'])->name('membership');

            Route::get('/{id}/{slug}', [SpaceController::class, 'SpacesView'])->name('view');
        });
    });

// --- Email Verification Route ---
Route::get('/verify-email', function () {
    if (Auth::user()->email_verified_at) {
        return inertia('App/Verify/EmailVerified');
    } elseif (Auth::user()->settings->verified_email) {
        return inertia('App/Verify/EmailSent');
    } else {
        return inertia('App/Verify/Email');
    }
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    UserSettings::where('id', Auth::id())->update(['verified_email' => false]);
    $request->fulfill();

    return redirect('/my/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    if (app()->environment() === 'production') {
        UserSettings::where('id', Auth::id())->update(['verified_email' => true]);
        if (!$request->user()->hasVerifiedEmail()) {
            // Dispatch the SendVerificationEmail job to the queue
            Queue::push(new SendVerificationEmail($request->user()));
        }
    } else {
        User::where('id', Auth::id())->update(['email_verified_at' => now()]);
    }
})->middleware('throttle:6,1')->name('verification.send');

// --- Policy Routes ---
Route::group(['as' => 'policies.', 'prefix' => 'policies'], function () {
    Route::get('/terms-of-service', [PolicyController::class, 'TermsOfService'])->name('tos');
    Route::get('/privacy', [PolicyController::class, 'PrivacyPolicy'])->name('privacy');
});


// --- Admin Routes ---
$adminRoutes = function () {
    Route::group(['as' => 'admin.', 'middleware' => [Admin::class, EnsurePasswordIsConfirmed::class, 'verified']], function () {
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
    });
};

if (config('app.env') === 'production') {
    Route::domain(config('app.admin.domain'))
        ->middleware(['cacheable'])
        ->group($adminRoutes);
} else {
    Route::prefix('admin')
        ->middleware(['cacheable'])
        ->group($adminRoutes);
}
