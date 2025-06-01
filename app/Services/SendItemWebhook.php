<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use App\Models\Item;
class SendItemWebhook
{
    public function sendDiscordNotification(Item $item)
    {
        $formattedType = ucfirst($item->item_type);
        $siteName = config('Values.name');
        DiscordAlert::message("New {$formattedType}!", [
            [
                'content' => $item->name,
                'username' => "{$siteName} Item Notifier",
                'embeds' => [
                    [
                        'type' => 'rich',
                        'description' => "$item->description",
                        'url' => route('store.item', $item->id),
                        'timestamp' => $item->created_at->diffForHumans(),
                        'color' => hexdec('3366ff'),
                        'footer' => [
                            'text' => "© {$siteName}",
                            'icon_url' => config('Values.icon'),
                        ],
                        // Thumbnail
                        'image' => [
                            'url' => 'img',
                        ],
                        "thumbnail" => [
                            "url" => $item->thumbnail(),
                        ],
                        'author' => [
                            'name' => $item->creator->username,
                            'url' => route('user.profile', $item->creator->username),
                        ],
                    ],
                ],
            ]
        ]);
    }
}
