<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Registration Role
    |--------------------------------------------------------------------------
    |
    | Every newly registered member will be assigned this role automatically.
    | Make sure this role exists in your seeder.
    |
    */
    'default_registration_role' => env('DEFAULT_REGISTRATION_ROLE', 'student'),

    /*
    |--------------------------------------------------------------------------
    | Email-Based Role Rules
    |--------------------------------------------------------------------------
    |
    | On registration, the first matching regex pattern will be used to
    | assign a role. If none match, default_registration_role is applied.
    |
    */
    'email_role_rules' => [
        ['pattern' => env('ROLE_RULE_ADMIN_PATTERN', '/^admin@/i'), 'role' => 'admin'],
        ['pattern' => env('ROLE_RULE_SUPPORT_PATTERN', '/^(support|help)@/i'), 'role' => 'support'],
        ['pattern' => env('ROLE_RULE_INSTRUCTOR_PATTERN', '/^(trainer|instructor)@/i'), 'role' => 'instructor'],
    ],
];
