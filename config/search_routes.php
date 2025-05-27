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
        'url' => 'route("forum.index")',
        'description' => 'Engage with the community, ask questions, and share insights.',
        'type' => 'route',
        'icon' => 'fas fa-comments',
    ],
    [
        'name' => 'About Us',
        'keywords' => ['about', 'company', 'information', 'team', 'contact'],
        'url' => 'route("about.index")',
        'description' => 'Learn more about our company and what we do.',
        'type' => 'route',
        'icon' => 'fad fa-info-circle',
    ],
    [
        'name' => 'Dashboard',
        'keywords' => ['dashboard', 'account', 'settings', 'profile'],
        'url' => 'route("my.dashboard")',
        'description' => 'Access your personal dashboard.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ],
    // Add more as needed
];
