<?php

namespace App\Http\Controllers\Chat;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Notifications\MessageNotification;

class ChatController extends Controller
{
    public function ChatIndex(int $userID)
    {
        $toUser = User::where('id', $userID)->first();

        if (!$toUser) {
            abort(404);
        }

        if ($toUser->id == Auth::id()) {
            abort(403);
        }

        return inertia('Inbox/Chat', [
            'toUser' => $toUser,
        ]);
    }

    public function fetchMessages(int $userID)
    {
        $chats = Message::where(function ($query) use ($userID) {
            $query->where('sent_from', Auth::id())
                ->where('sent_to', $userID);
        })
            ->orWhere(function ($query) use ($userID) {
                $query->where('sent_from', $userID)
                    ->where('sent_to', Auth::id());
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc') // changed to created_at to match the timestamp used in getTimeAttribute
            ->get();

        return response()->json([
            'chats' => $chats,
        ]);
    }

    public function sendMessage(int $userID, Request $request)
    {
        $toUser = User::where('id', $userID)->first();
        if ($toUser->id == Auth::id()) {
            return response()->json(
                [
                    "message" => "You cannot send messages to yourself.",
                    "type" => "danger",
                ],
                502
            );
        }

        $message = Message::create([
            'sent_from' => Auth::id(),
            'sent_to'   => $toUser->id,
            'is_system_message' => $toUser->id === config("Values.system_account_id") ?? false,
            'message'   => $request->message,
        ]);

        broadcast(new MessageSent($message));

        $toUser->notify(new MessageNotification(Auth::user()));

        return response()->json([
            'type' => 'success',
            'message' => 'Your message to ' . $toUser->username . ' has been sent.',
        ]);
    }
}
