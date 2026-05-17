<?php

declare(strict_types=1);

return [
    'common' => [
        'tim_theday' => 'TheDay Team',
    ],
    'flash' => [
        'purchase_error' => 'Payment could not be processed. Please try again or contact support.',
        'claim_success'  => 'Premium activated! Check your dashboard for details.',
        'created'        => 'Gift code :code has been created.',
        'deleted'        => 'Gift deleted.',
        'cannot_delete'  => 'Only gifts with pending status can be deleted.',
    ],
    'validation' => [
        'plan_invalid' => 'Invalid plan for a gift.',
    ],
    'mail' => [
        'received_subject' => 'You received a premium gift from TheDay!',
        'claimed_subject'  => 'Your gift has been claimed!',
        'received_heading' => 'Congratulations! You received a premium gift 🎁',
        'received_greeting' => 'Hello,',
        'received_intro'   => 'sent you Premium :plan access for :days days.',
        'received_cta'     => 'Claim Your Gift Now',
        'received_link_prefix' => 'Or open this link:',
        'received_expires' => 'This gift is valid until :date. After that the code will expire.',
        'claimed_heading'  => 'Your gift has been claimed!',
        'claimed_greeting' => 'Hello :name,',
        'claimed_body'     => ':recipient (:email) just claimed your premium gift on :date.',
        'claimed_thanks'   => 'Thank you for spreading joy!',
    ],
];
