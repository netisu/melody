<?php
return [
    'name' => 'Netisu',
    'logo' => '/assets/img/netisu-logo.svg',
    'icon' => '/assets/img/logo.svg',
    'marketLogo' => '/assets/img/netisu-logo.svg',
    'minimum_age_without_consent' => 13,

    'in_event' => true,
    'system_account_id' => 2,
    'in_testing_phase' => false,

    'version' => "melody",
    'frontend' => [
        'page_loader' => false,
        'sidebar_menu' => false,
        'search_bar' => true,
        'loading_messages' => false,
    ],

    'price' => [
        'username' => '250',
    ],

    'production' => [
        'domains' => [
            'main' => 'https://netisu.com',
            'storage' => 'https://cdn.netisu.com/',
            'api' => 'https://api.netisu.com/',
            'careers' => 'https://careers.netisu.com',
            'support' => 'aeo@netisu.com'
        ],
    ],

    'shop' => [
        'previews_enabled' => true,
        'preview_types' => ['hat', 'addon', 'face', 'tool'],
        'threed_types' => ['hat', 'addon', 'tool']
    ],

    'socials' => [
        'discord' => 'https://discord.gg/Zec49GD6ps',
        'twitter' => 'https://x.com/PlayNetisu',
        'twitch' => '',
        'tiktok' => '',
        'facebook' => ''
    ],
];
