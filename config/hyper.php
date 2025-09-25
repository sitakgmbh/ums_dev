<?php

return [
    'menu' => [
        [
            'title' => 'Navigation',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'url' => '/dashboard',
                    'icon' => 'mdi mdi-home',
                ],
                [
                    'label' => 'Eröffnungen',
                    'url' => '/eroeffnungen',
                    'icon' => 'mdi mdi-account-plus',
                ],
            ],
        ],
        [
            'title' => 'Admin',
            'items' => [
                [
                    'label' => 'Active Directory Benutzer',
                    'url' => '/admin/ad-users',
                    'icon' => 'mdi mdi-account-multiple',
                ],
                [
                    'label' => 'Systemsteuerung',
                    'url' => '/admin',
                    'icon' => 'mdi mdi-hammer-screwdriver',
                ],
            ],
        ],
    ]
];
