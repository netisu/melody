<?php

namespace App\Http\Controllers\Endpoints;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function NotificationIndex()
    {
        // page soon

        return inertia('Welcome', [
            'LandingItems' => $landingItems,
            'LandingPosts' => $landingPosts,
        ]);
    }

    public function ReadAll()
    {
        $user = Auth::user();

        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'type' => 'success',
            'message' => 'All Notifications Read.',
        ]);
    }
}
