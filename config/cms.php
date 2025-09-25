<?php

return [
    'routing' => [
        'mode' => env('CMS_MODE', 'path'),
        'prefix' => 'cms',
    ],
    'guard' => 'web',

    'navigation' => [
        'route' => 'cms.dashboard',
        'icon'  => 'heroicon-o-rectangle-stack',
        'order' => 30,
    ],

    'sidebar' => [
        [
            'group' => 'Allgemein',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'cms.dashboard',
                    'icon'  => 'heroicon-o-home',
                ],
                [
                    'label' => 'Board anlegen',
                    'route' => 'cms.boards.create',
                    'icon'  => 'heroicon-o-plus',
                ],  
            ],
        ],
        [
            'group' => 'Boards',
            'dynamic' => [
                'model'     => \Platform\Cms\Models\CmsBoard::class,
                'team_based' => true,
                'order_by'  => 'name',
                'route'     => 'cms.boards.show',
                'icon'      => 'heroicon-o-folder',
                'label_key' => 'name',
            ],
        ],
    ],
    // Billables vorerst leer für CMS
    'billables' => []
];