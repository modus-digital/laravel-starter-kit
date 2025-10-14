<?php

declare(strict_types=1);

return [
    'open' => 'Open',
    'close' => 'Close',
    'never' => 'Never',
    'unknown' => 'Unknown',
    'cancel' => 'Cancel',
    'save_changes' => 'Save Changes',
    'saved' => 'Saved',
    'go_back' => 'Go Back',
    'confirm' => 'Confirm',
    'remove' => 'Remove',
    'delete' => 'Delete',
    'actions' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'close' => 'Close',
    ],

    'messages' => [
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Information',
    ],

    'placeholders' => [
        'email' => 'Enter your email',
        'name' => 'Enter your name',
        'password' => 'Enter your password',
    ],

    'modals' => [
        'confirmable-password' => [
            'title' => 'Confirm Password',
            'description' => 'Please enter your password to confirm this action.',
        ],
    ],

    'notifications' => [
        'account_created' => [
            'line1' => 'Welcome to :app! Your account has been created successfully.',
            'password_line' => 'Your temporary password is: **:password**',
            'action' => 'Login to Your Account',
            'line2' => 'Please change your password after your first login.',
        ],
        'password_reset' => [
            'line1' => 'Your password has been reset successfully.',
            'action' => 'Login to Your Account',
            'line2' => 'You can now sign in with your new password.',
        ],
    ],
];
