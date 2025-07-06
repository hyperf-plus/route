<?php

declare(strict_types=1);

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
            BASE_PATH . '/packages',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
    ],
];