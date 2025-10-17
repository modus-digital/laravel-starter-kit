# Hook System Documentation

The hook system allows plugins and modules to inject content at specific locations throughout the application without modifying core files.

## Available Hooks

### Authentication Form Hooks

#### Login Form

-   **`AUTH_LOGIN_FORM_BEFORE`** - Before the login form

    -   Location: `resources/views/livewire/auth/login.blade.php`
    -   Use case: Welcome messages, announcements, promotional banners

-   **`AUTH_LOGIN_FORM_AFTER`** - After the login form
    -   Location: `resources/views/livewire/auth/login.blade.php`
    -   Use case: Security notices, help links, additional information

#### Register Form

-   **`AUTH_REGISTER_FORM_BEFORE`** - Before the registration form

    -   Location: `resources/views/livewire/auth/register.blade.php`
    -   Use case: Terms of service, promotional messages

-   **`AUTH_REGISTER_FORM_AFTER`** - After the registration form
    -   Location: `resources/views/livewire/auth/register.blade.php`
    -   Use case: Additional information, privacy notices

#### Forgot Password Form

-   **`AUTH_FORGOT_PASSWORD_FORM_BEFORE`** - Before the forgot password form

    -   Location: `resources/views/livewire/auth/forgot-password.blade.php`
    -   Use case: Help text, alternative recovery methods

-   **`AUTH_FORGOT_PASSWORD_FORM_AFTER`** - After the forgot password form
    -   Location: `resources/views/livewire/auth/forgot-password.blade.php`
    -   Use case: Support contact information

#### Reset Password Form

-   **`AUTH_RESET_PASSWORD_FORM_BEFORE`** - Before the reset password form

    -   Location: `resources/views/livewire/auth/reset-password.blade.php`
    -   Use case: Password requirements, security tips

-   **`AUTH_RESET_PASSWORD_FORM_AFTER`** - After the reset password form
    -   Location: `resources/views/livewire/auth/reset-password.blade.php`
    -   Use case: Additional security recommendations

#### Verify Email

-   **`AUTH_VERIFY_EMAIL_FORM_BEFORE`** - Before email verification actions

    -   Location: `resources/views/livewire/auth/verify-email.blade.php`
    -   Use case: Verification instructions, troubleshooting tips

-   **`AUTH_VERIFY_EMAIL_FORM_AFTER`** - After email verification actions
    -   Location: `resources/views/livewire/auth/verify-email.blade.php`
    -   Use case: Support information

### Layout Hooks

#### Body Hooks (Guest & App Layouts)

-   **`BODY_START`** - Immediately after opening `<body>` tag

    -   Locations:
        -   `resources/views/components/layouts/guest.blade.php`
        -   `resources/views/components/layouts/app.blade.php`
    -   Use case: Analytics scripts, tracking pixels, browser notifications

-   **`BODY_END`** - Before closing `</body>` tag
    -   Locations:
        -   `resources/views/components/layouts/guest.blade.php`
        -   `resources/views/components/layouts/app.blade.php`
    -   Use case: Chat widgets, footer scripts, analytics

#### App Layout Hooks

-   **`HEADER`** - Before the main header component

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Announcement bars, system messages, custom headers

-   **`SIDEBAR`** - Before the sidebar component

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Custom sidebar content, widgets

-   **`SIDEBAR_MENU`** - Inside the sidebar menu (after default menu items)

    -   Location: `resources/views/components/layouts/navigation/sidebar.blade.php`
    -   Use case: Additional menu items, plugin navigation links

-   **`CONTENT_BEFORE`** - Before the main content area (outside main tag)

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Breadcrumbs, page-level announcements

-   **`CONTENT_START`** - At the start of main content (inside main tag)

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Page-specific headers, alerts

-   **`CONTENT_END`** - At the end of main content (inside main tag)

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Additional page content, CTAs

-   **`CONTENT_AFTER`** - After the main content area (outside main tag)

    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Floating widgets, help buttons

-   **`FOOTER`** - Footer area (before scripts)
    -   Location: `resources/views/components/layouts/app.blade.php`
    -   Use case: Footer content, copyright notices

## Usage Examples

### 1. Registering a Hook in a Service Provider

```php
<?php

namespace Modules\MyPlugin\Providers;

use App\Enums\Hooks;
use App\Support\Facades\Hooks as HooksFacade;
use Illuminate\Support\ServiceProvider;

class MyPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Simple string content
        HooksFacade::register(
            Hooks::AUTH_LOGIN_FORM_BEFORE,
            '<div class="alert">Welcome!</div>',
            10  // priority (optional, default: 10)
        );

        // Using a view
        HooksFacade::register(
            Hooks::SIDEBAR_MENU,
            fn () => view('my-plugin::menu-items')
        );

        // Using a closure with logic
        HooksFacade::register(
            Hooks::CONTENT_START,
            function () {
                if (auth()->user()->isAdmin()) {
                    return view('my-plugin::admin-notice');
                }
                return '';
            }
        );
    }
}
```

### 2. Priority System

```php
// High priority (renders first)
HooksFacade::prepend(Hooks::SIDEBAR_MENU, '<li>First Item</li>');

// Normal priority (default: 10)
HooksFacade::register(Hooks::SIDEBAR_MENU, '<li>Middle Item</li>');

// Low priority (renders last)
HooksFacade::append(Hooks::SIDEBAR_MENU, '<li>Last Item</li>');
```

### 3. Adding Menu Items

```php
HooksFacade::register(
    Hooks::SIDEBAR_MENU,
    fn () => view('my-plugin::navigation', [
        'icon' => 'heroicon-o-users',
        'label' => 'My Plugin',
        'route' => 'my-plugin.index',
    ])
);
```

### 4. Context-Aware Content

```php
HooksFacade::register(
    Hooks::CONTENT_START,
    function () {
        $user = auth()->user();

        if ($user->hasUnreadNotifications()) {
            return view('my-plugin::notification-banner', [
                'count' => $user->unreadNotifications()->count()
            ]);
        }

        return '';
    }
);
```

## Testing Hooks

Your hook system comes with comprehensive tests. To run them:

```bash
php artisan test --filter=HookManager
```

## Best Practices

1. **Use Views for Complex Content**: Don't put HTML in service providers
2. **Check Permissions**: Verify user authorization before rendering content
3. **Performance**: Keep hook content lightweight
4. **Priorities**: Use explicit priorities when order matters
5. **Cleanup**: Clear hooks when appropriate (e.g., in tests)

## Managing Hooks

### Check if a Hook Has Content

```php
if (Hooks::has(Hooks::SIDEBAR_MENU)) {
    // Hook has registered content
}
```

### Clear a Specific Hook

```php
Hooks::clear(Hooks::SIDEBAR_MENU);
```

### Clear All Hooks

```php
Hooks::flush();
```

## Quick Reference

| Hook Location          | Enum Constant               | Use Case                        |
| ---------------------- | --------------------------- | ------------------------------- |
| Login Form (before)    | `AUTH_LOGIN_FORM_BEFORE`    | Announcements, welcome messages |
| Login Form (after)     | `AUTH_LOGIN_FORM_AFTER`     | Security notices, help links    |
| Register Form (before) | `AUTH_REGISTER_FORM_BEFORE` | Terms, promotional content      |
| Register Form (after)  | `AUTH_REGISTER_FORM_AFTER`  | Privacy notices                 |
| Body Start             | `BODY_START`                | Analytics, tracking             |
| Body End               | `BODY_END`                  | Chat widgets, scripts           |
| Header                 | `HEADER`                    | Announcement bars               |
| Sidebar                | `SIDEBAR`                   | Custom sidebar content          |
| Sidebar Menu           | `SIDEBAR_MENU`              | Additional menu items           |
| Content Before         | `CONTENT_BEFORE`            | Breadcrumbs                     |
| Content Start          | `CONTENT_START`             | Page headers, alerts            |
| Content End            | `CONTENT_END`               | Additional page content         |
| Content After          | `CONTENT_AFTER`             | Floating widgets                |
| Footer                 | `FOOTER`                    | Footer content                  |
