<?php

return [
    // Validate that the email domain has MX/A records (blocks made-up domains)
    'check_dns' => env('GUEST_PASS_CHECK_DNS', true),

    // How long the entered email + mobile stay valid for buying passes in this browser session
    'details_ttl_minutes' => 60,

    'max_persons_per_purchase' => 50,

    // Throwaway / placeholder domains that are never accepted
    'blocked_domains' => [
        'example.com', 'example.org', 'example.net', 'test.com', 'noemail.com', 'sathwaracommunity.org',
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'sharklasers.com', 'grr.la',
        '10minutemail.com', '10minutemail.net', 'tempmail.com', 'temp-mail.org', 'temp-mail.io',
        'yopmail.com', 'yopmail.net', 'trashmail.com', 'throwawaymail.com', 'getnada.com', 'nada.email',
        'maildrop.cc', 'dispostable.com', 'fakeinbox.com', 'mintemail.com', 'moakt.com', 'emailondeck.com',
        'mohmal.com', 'tempail.com', 'burnermail.io', 'mailnesia.com', 'spamgourmet.com', 'tempinbox.com',
    ],
];
