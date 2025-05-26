<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Login
    'login' => [
        'title' => 'Sign in to your account',
        'email' => 'Your email',
        'password' => 'Password',
        'remember' => 'Remember me',
        'forgot_password' => 'Forgot your password?',
        'sign_in' => 'Sign in',
        'no_account' => 'Don\'t have an account yet?',
        'sign_up' => 'Sign up',
        'invalid_credentials' => 'Invalid credentials',
    ],

    // Registration
    'register' => [
        'title' => 'Create an account',
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'sign_up' => 'Sign up',
        'already_have_account' => 'Already have an account?',
        'sign_in' => 'Sign in',
    ],

    // Email verification
    'verify' => [
        'title' => 'Verify your email',
        'message' => 'Please confirm your email before continuing.',
        'resent' => 'A new verification link has been sent to your email address.',
        'request_another' => 'click here to request another',
        'before_proceeding' => 'Before proceeding, please check your email for a verification link. If you did not receive the email.',
        'request_another_link' => 'click here to request another',
    ],

    // Password reset
    'password_reset' => [
        'token' => [
            'title' => 'Reset password',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Confirm password',
            'reset_password' => 'Reset Password',
        ],
        'confirm' => [
            'title' => 'Confirm password',
            'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
            'password' => 'Password',
            'confirm_password' => 'Confirm password',
            'confirm_button' => 'Confirm',
        ],
        'request' => [
            'title' => 'Forgot your password?',
            'email' => 'Email',
            'password' => 'Password',
            'reset_password' => 'Reset Password',
            'forgot_password' => 'Forgot your password?',
            'already_have_account' => 'Already have an account?',
            'sign_in' => 'Sign in',
        ],
    ],

    // Two-factor authentication
    'two_factor' => [
        'verify' => [
            'invalid_code' => 'Invalid code',
            'title' => 'Verify your code',
            'message' => 'Please enter the 6-digit code from your authenticator app.',
            'button' => 'Verify',
            'use_recovery_code' => 'Use a recovery code',
        ],
        'recover' => [
            'title' => 'Recover your account',
            'description' => 'Please enter the recovery code from your authenticator app.',
            'recovery_code' => 'Recovery code',
            'button' => 'Recover',
        ],
    ],

    'rbac' => [
        'role' => [
            'super_admin' => [
                'title' => 'Super Administrator',
                'description' => 'As super administrator you have access to all features and settings of the application.',
            ],
            'admin' => [
                'title' => 'Administrator',
                'description' => 'As administrator you have access to all features and settings of the application.',
            ],
            'user' => [
                'title' => 'User',
                'description' => 'As user you have standard access to the application.',
            ],
        ],
    ],

];
