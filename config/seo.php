<?php

declare(strict_types=1);

return [
    'breadcrumbs' => [
        'view' => 'seo::breadcrumbs',
    ],

    'robots' => [
        'enabled' => true,
        'user_agents' => [
            '*' => [
                'allow' => ['/'],
                'disallow' => ['/admin', '/horizon', '/telescope'],
            ],
        ],
        'sitemap' => true,
    ],

    'sitemap' => [
        'enabled' => true,
        'route' => '/sitemap.xml',
        'cache' => 3600,
        'urls' => [],
        'models' => [],
    ],
];
