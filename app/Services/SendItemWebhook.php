<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SendItemWebhook
{
public function sendDiscordNotification(string $itemType, string $name, string $description, array $formattedPrice, string $thumbnail, string $link)
    {
        $webhookUrl = config('app.DISCORD_ALERT_WEBHOOK');

        $timestamp = date("c", strtotime("now"));
        $priceString = implode(' | ', array_filter($formattedPrice)); // Remove empty elements

        $messageData = [
            'content' => "New " . ucfirst($itemType) . "!",
            'username' => config('site.name') . ' Item Notifier',
            'embeds' => [
                [
                    'title' => $name,
                    'type' => 'rich',
                    'description' => "$description - $priceString",
                    'url' => $link,
                    'timestamp' => $timestamp,
                    'color' => hexdec('3366ff'),
                    'footer' => [
                        'text' => '&copy; ' . config('Values.name'),
                        'icon_url' => config('Values.icon'),
                    ],
                    // Thumbnail
                    'image' => [
                        'url' => 'img',
                    ],
                    "thumbnail" => [
                         "url" => $thumbnail,
                    ],
                    'author' => [
                        'name' => config('Values.name') . ' Item Notifier',
                        'url' => config('Values.production.domains.main'),
                    ],
                ],
            ],
        ];

        $response = Http::post($webhookUrl, $messageData);

        // Handle response if needed
        return $response;
    }
}
