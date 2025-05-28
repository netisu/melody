<?php

return [
    [
        'name' => 'Marketplace',
        'keywords' => ['market', 'store', 'shop', 'marketplace', 'buy', 'sell', 'products'],
        'url' => 'route("store.page")',
        'description' => 'Explore various items for sale and discover new products.',
        'type' => 'route',
        'icon' => 'fad fa-store',
    ],
    [
        'name' => 'Discuss',
        'keywords' => ['forum', 'discussions', 'community', 'posts', 'questions', 'answers'],
        'url' => 'route("forum.page", ["id" => 1])',
        'description' => 'Engage with the community, ask questions, and share insights.',
        'type' => 'route',
        'icon' => 'fas fa-comments',
    ],
    [
        'name' => 'Dashboard',
        'keywords' => ['dashboard', 'account', 'settings', 'profile'],
        'url' => 'route("my.dashboard.page")',
        'description' => 'Access your personal dashboard.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ],
    [
        'name' => 'Discover',
        'keywords' => ['users', 'discover', 'players', 'online'],
        'url' => 'route("my.dashboard.page")',
        'description' => 'Access your personal dashboard.',
        'type' => 'route',
        'icon' => 'fad fa-user-group',
    ],
    // Add more as needed
];
