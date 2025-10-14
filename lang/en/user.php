<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | User Language Lines
    |--------------------------------------------------------------------------
    */
    'sessions' => [
        'title' => 'Browser Sessions',
        'password' => 'Password',
        'logout_other_sessions' => 'Log out other browser sessions',
        'this_device' => 'This device',
        'last_active' => 'Last active',
        'none' => 'No active sessions found.',
        'messages' => [
            'none_to_clear' => 'No other sessions to clear.',
            'cleared' => 'All other browser sessions have been cleared.',
            'failed' => 'Failed to clear browser sessions.',
        ],
    ],

    'avatar' => [
        'change' => 'Change avatar',
        'modal_title' => 'Change Avatar',
        'title' => 'Change Avatar',
        'description' => 'Upload a new profile picture. Accepted formats: PNG, JPG, GIF, WEBP (max 5MB)',
        'processing' => [
            'title' => 'Processing image...',
            'message' => 'Please wait while we upload your file',
        ],
        'form' => [
            'label' => 'Profile Picture',
            'save' => 'Save Avatar',
            'saving' => 'Saving…',
            'remove' => 'Remove Avatar',
            'confirm_remove' => 'Are you sure you want to remove your avatar?',
        ],
        'messages' => [
            'upload_failed' => 'Failed to upload avatar.',
            'updated' => 'Avatar updated successfully!',
            'removed' => 'Avatar removed successfully!',
        ],
        'default_alt' => 'User',
    ],

    'profile' => [
        'card' => [
            'title' => 'Profile overview',
            'placeholder_name' => 'Your name',
            'placeholder_email' => 'example@email.com',
            'delete_account' => [
                'button' => 'Delete Account',
                'title' => 'Delete Account',
                'description' => 'This action will permanently delete your account and all associated data. Please enter your current password to continue.',
                'password_label' => 'Current Password',
                'confirm' => 'Permanently delete',
            ],
        ],
        'two_factor' => [
            'title' => 'Two-factor authentication',
            'status' => 'Two-factor status',
            'provider' => 'Two-factor provider',
            'recovery_codes' => 'Recovery codes',
        ],
        'display' => [
            'title' => 'Display',
            'appearance' => 'Appearance',
            'theme' => 'Theme',
        ],
        'preferences' => [
            'title' => 'Preferences',
            'language' => 'Language',
            'date_format' => 'Date Format',
            'time_format' => 'Time Format',
            'timezone' => 'Timezone',
            'date_formats' => [
                'mdy' => 'MM/DD/YYYY',
                'dmy' => 'DD/MM/YYYY',
                'ymd' => 'YYYY-MM-DD',
            ],
            'time_formats' => [
                '12h' => '12-hour (AM/PM)',
                '24h' => '24-hour',
            ],
        ],
        'update_password' => [
            'title' => 'Update Password',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_new_password' => 'Confirm New Password',
            'button' => 'Save password',
        ],
        'overview' => [
            'localization' => [
                'title' => 'Localization Settings',
                'language' => 'Language',
                'date_format' => 'Date Format',
                'time_format' => 'Time Format',
                'timezone' => 'Timezone',
            ],
            'security' => [
                'title' => 'Security Settings',
                'two_factor_status' => 'Two-factor status',
                'two_factor_provider' => 'Two-factor provider',
                'password_last_updated' => 'Password last updated',
            ],
            'display' => [
                'title' => 'Display Settings',
                'appearance' => 'Appearance',
                'theme' => 'Theme',
            ],
        ],
    ],
];
