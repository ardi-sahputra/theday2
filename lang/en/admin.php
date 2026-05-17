<?php

declare(strict_types=1);

return [
    'plans' => [
        'flash' => [
            'updated' => 'Plan :name updated successfully.',
        ],
    ],
    'discounts' => [
        'flash' => [
            'created' => "Discount ':label' created successfully.",
            'updated' => "Discount ':label' updated successfully.",
            'deleted' => "Discount ':label' deleted successfully.",
            'cannot_delete_active' => 'An active discount cannot be deleted. Wait until the period ends, or set ends_at to a past date.',
        ],
    ],
];
