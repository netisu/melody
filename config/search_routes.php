<?php

use Illuminate\Support\Facades\Auth;

$searchRoutes = [
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
        'name' => 'Discover',
        'keywords' => ['users', 'discover', 'players', 'online'],
        'url' => 'route("user.page")',
        'description' => 'Discover new users.',
        'type' => 'route',
        'icon' => 'fad fa-user-group',
    ],
];

if (Auth::check() && Auth::user()) {
    $searchRoutes[] = [[
        'name' => 'Dashboard',
        'keywords' => ['dashboard', 'home', 'stats', 'analytics'],
        'url' => 'route("my.dashboard.page")',
        'description' => 'Access your personal dashboard.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ], [
        'name' => 'Money',
        'keywords' => ['money', 'purchases', 'transactions', 'sales'],
        'url' => 'route("my.money.page")',
        'description' => 'Manage your finances and convert your currencies.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ], [
        'name' => 'Your Profile',
        'keywords' => ['profile', '', 'transactions', 'sales'],
        'url' => 'route("my.money.page")',
        'description' => 'Manage your finances and convert your currencies.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ], [
        'name' => 'Settings',
        'keywords' => ['account', 'settings', 'profile', 'password', 'appearence', 'language'],
        'url' => 'route("my.money.page")',
        'description' => 'Manage your finances and convert your currencies.',
        'type' => 'route',
        'icon' => 'fad fa-tachometer-alt',
    ]];
};

return $searchRoutes;
