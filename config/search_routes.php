<?php
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

return (Array) $searchRoutes;
