<?php

declare(strict_types=1);

namespace App\Enums;

enum Hooks: string
{
    case AUTH_LOGIN_FORM_BEFORE = 'auth_login_form_before';
    case AUTH_LOGIN_FORM_AFTER = 'auth_login_form_after';

    case AUTH_REGISTER_FORM_BEFORE = 'auth_register_form_before';
    case AUTH_REGISTER_FORM_AFTER = 'auth_register_form_after';

    case AUTH_FORGOT_PASSWORD_FORM_BEFORE = 'auth_forgot_password_form_before';
    case AUTH_FORGOT_PASSWORD_FORM_AFTER = 'auth_forgot_password_form_after';

    case AUTH_RESET_PASSWORD_FORM_BEFORE = 'auth_reset_password_form_before';
    case AUTH_RESET_PASSWORD_FORM_AFTER = 'auth_reset_password_form_after';

    case AUTH_VERIFY_EMAIL_FORM_BEFORE = 'auth_verify_email_form_before';
    case AUTH_VERIFY_EMAIL_FORM_AFTER = 'auth_verify_email_form_after';

    case BODY_START = 'body_start';
    case BODY_END = 'body_end';

    case CONTENT_AFTER = 'content_after';
    case CONTENT_BEFORE = 'content_before';
    case CONTENT_START = 'content_start';
    case CONTENT_END = 'content_end';

    case FOOTER = 'footer';
    case HEADER = 'header';

    case SIDEBAR = 'sidebar';
    case SIDEBAR_MENU = 'sidebar_menu';
}
