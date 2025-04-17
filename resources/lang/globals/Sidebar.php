<?php

return [
    [
        'url' => '#',
        'active_link' => 'games.*',
        'section' => 'NAVIGATION',
        'icon' => 'fas fa-gamepad-modern',
        'title' => [
            'en' => 'Games',
            'ru' => 'Игры',
            'jp' => 'ゲーム',
        ],
    ],
    [
        'url' => route('store.page'),
        'section' => 'NAVIGATION',
        'active_link' => 'store.*',
        'icon' => 'fas fa-store',
        'title' => [
            'en' => 'Market',
            'ru' => 'Рынок',
            'jp' => '市場',
        ],
    ],
    [
        'url' => route('forum.page', ['id' => 1]),
        'active_link' => 'forum.page.*',
        'icon' => 'fas fa-messages',
        'section' => 'NAVIGATION',
        'title' => [
            'en' => 'Discuss',
            'ru' => 'Обсуждать',
            'jp' => '議論',
        ],
    ],
    // ... other items
    [
        'url' => '#',
        'section' => 'BOOST YOUR ACCOUNT',
        'active_link' => 'upgrade.*',
        'icon' => 'fas fa-rocket-launch',
        'title' => [
            'en' => 'Upgrade',
            'ru' => 'модернизировать',
            'jp' => 'アップグレード',
        ],
    ],
];
