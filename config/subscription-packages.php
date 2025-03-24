<?php

return [
    'e-book-subscription' => [
        'package_limits' => [
            'max_view' => [
                'category' => 'paid_ebook',
                'value' => 30, // Maximum 30 views
                'duration' => 1, // Duration in months
                'unit' => 'month'
            ]
        ]
    ]
];
