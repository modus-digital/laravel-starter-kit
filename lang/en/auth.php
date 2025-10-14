<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    /*
    |--------------------------------------------------------------------------
    | Authentication Pages Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication pages for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'login' => [
        'title' => 'Login',
        'page_title' => 'Login',
        'email' => 'Email',
        'password' => 'Password',
        'remember' => 'Remember Me',
        'forgot_password' => 'Forgot Password?',
        'sign_in' => 'Sign In',
        'sign_out' => 'Sign out',
        'no_account' => 'Don\'t have an account?',
        'sign_up' => 'Sign Up',
    ],

    'register' => [
        'title' => 'Register',
        'page_title' => 'Register',
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Password Confirmation',
        'sign_up' => 'Sign Up',
        'already_have_account' => 'Already have an account?',
        'sign_in' => 'Sign In',
    ],

    'forgot_password' => [
        'title' => 'Forgot Password',
        'page_title' => 'Forgot Password',
        'description' => 'Enter your email address and we\'ll send you a link to reset your password.',
        'email' => 'Email',
        'send_reset_link' => 'Send Reset Link',
        'reset_link_sent' => 'We have emailed your password reset link.',
        'already_have_account' => 'Already have an account?',
        'sign_in' => 'Sign In',
    ],

    'reset_password' => [
        'title' => 'Reset Password',
        'page_title' => 'Reset Password',
        'description' => 'Enter your new password below.',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Password Confirmation',
        'reset_password' => 'Reset Password',
        'password_reset_success' => 'Your password has been reset successfully.',
        'already_have_account' => 'Already have an account?',
        'sign_in' => 'Sign In',
    ],

    'verification' => [
        'title' => 'Verify Your Email',
        'page_title' => 'Verify Email',
        'message' => 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',
        'link_sent' => 'A new verification link has been sent to your email address.',
        'resend' => 'Resend Verification Email',
    ],

    'logout' => 'Logout',

    'two_factor' => [
        'email' => [
            'subject' => 'Your Two-Factor Authentication Code',
            'greeting' => 'Hello :name,',
            'line1' => 'Your two-factor authentication verification code is:',
            'line2' => 'This code will expire in :minutes minutes.',
            'line3' => 'If you did not request this code, please ignore this email or contact support if you have concerns about your account security.',
            'salutation' => 'Best regards',
            'messages' => [
                'code_sent' => 'Verification code sent to your email address.',
                'code_invalid' => 'The verification code is invalid. Please try again.',
                'code_expired' => 'The verification code has expired. Request a new code to continue.',
                'code_attempts_exceeded' => 'Too many invalid attempts. Request a new verification code.',
                'code_unverified' => 'Verify your email code before continuing.',
                'code_resend' => 'Resend code',
                'enabled' => 'Two-factor authentication is now enabled.',
                'missing_settings' => 'Unable to find your security settings. Please contact support.',
            ],
        ],
        'messages' => [
            'code_invalid' => 'The verification code is invalid. Please try again.',
            'code_unverified' => 'Verify your two-factor authentication code before continuing.',
            'code_recent' => 'Please wait a few seconds before trying another code.',
            'enabled' => 'Two-factor authentication is now enabled.',
            'disabled' => 'Two-factor authentication has been disabled.',
            'disable_confirmation' => 'Disabling two-factor authentication will remove your current provider and recovery codes. You can enable it again at any time.',
            'recovery_download' => 'Recovery codes ready to download.',
        ],
        'recover' => [
            'title' => 'Recover Two-Factor Authentication',
            'description' => 'Enter the recovery code to continue.',
            'button' => 'Recover',
            'invalid' => 'The recovery code is invalid. Please try again.',
            'placeholder' => 'A1B2-C3D4',
        ],
        'verify' => [
            'title' => 'Verify Two-Factor Authentication',
            'message' => 'Enter the 6-digit code from your authentication method.',
            'button' => 'Verify',
            'use_recovery_code' => 'Use a recovery code',
            'placeholder' => '000000',
        ],
        'wizard' => [
            'steps' => [
                'provider' => 'Provider',
                'verify' => 'Verify',
                'backup' => 'Backup',
                'done' => 'Done',
            ],
            'provider' => [
                'title' => 'Choose Authentication Method',
                'description' => 'Select how you want to receive your verification codes.',
                'options' => [
                    'email' => [
                        'title' => 'Email',
                        'description' => 'Receive verification codes via email when you sign in.',
                    ],
                    'authenticator' => [
                        'title' => 'Authenticator App',
                        'description' => 'Use an authenticator app like Google Authenticator or Authy.',
                    ],
                ],
            ],
            'email' => [
                'title' => 'Verify Your Email',
                'description' => 'We\'ll send a verification code to your email address.',
                'helper' => 'Click the button below to send a verification code to your registered email address.',
                'input_label' => 'Enter verification code',
                'placeholder' => 'Enter 6-digit code',
                'send_code' => 'Send Verification Code',
            ],
            'authenticator' => [
                'title' => 'Scan QR Code',
                'description' => 'Scan this QR code with your authenticator app.',
                'manual' => 'Or enter this code manually:',
                'input_label' => 'Enter 6-digit code',
                'helper' => 'Enter the 6-digit code from your authenticator app.',
            ],
            'recovery' => [
                'title' => 'Save Recovery Codes',
                'description' => 'Store these recovery codes in a safe place. You can use them to access your account if you lose access to your authentication method.',
                'notice_title' => 'Important',
                'notice_description' => 'Each recovery code can only be used once. Download or copy these codes now.',
                'download' => 'Download Recovery Codes',
                'confirm' => 'I\'ve saved my codes',
            ],
            'confirmation' => [
                'title' => 'All set!',
                'description' => 'Two-factor authentication has been successfully configured for your account.',
                'protected_message' => 'Your account is now protected with :provider two-factor authentication.',
                'next_steps_title' => 'Next steps',
                'next_steps' => [
                    'store_codes' => 'Keep your recovery codes in a safe place.',
                    'next_login' => 'You\'ll need to verify your identity on next login.',
                    'settings' => 'You can change or disable 2FA anytime in settings.',
                ],
                'finish' => 'Finish setup',
            ],
            'actions' => [
                'back' => 'Back',
                'verify_continue' => 'Verify & Continue',
                'continue' => 'Continue',
            ],
        ],
    ],
];
