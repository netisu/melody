<?php
return [
    'name' => 'Netisu',
    'logo' => '/assets/img/logo-theta.svg',
    'icon' => '/assets/img/logo.svg',
    'marketLogo' => '/assets/img/site-banners/market-title.png',

    'in_event' => true,

    'system_account_id' => 2,
    'in_testing_phase' => false,

    'version' => "melody",
    'frontend' => [
        'page_loader' => false,
        'sidebar_menu' => true,
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
        'preview_types' => ['hat', 'addon', 'face', 'tool']
    ],

    'socials' => [
        'discord' => 'https://discord.gg/netisu',
        'twitter' => '',
        'twitch' => '',
        'tiktok' => '',
        'facebook' => ''
    ],
];
