# Social Authentication Module

This module provides Socialite authentication through third-party providers like Google, GitHub, and Facebook for your Laravel application.

## Features

-   **Pre-configured Providers**: Providers (Google, GitHub, Facebook) are pre-seeded and cannot be created/deleted
-   **Credential Management Only**: Admins can only configure credentials, not provider settings
-   **Filament Admin UI**: Manage Socialite providers through the control panel
-   **Permission-based Access**: Requires `MANAGE_OAUTH_PROVIDERS` permission
-   **Hook Integration**: Social login buttons automatically appear on login page
-   **Encrypted Credentials**: Client secrets are encrypted in the database
-   **Flexible Provider Management**: Enable/disable providers and control display order

## Installation

The module is already installed and registered via `SocialAuthenticationServiceProvider`.

## Database Setup

Run the migrations and seeders:

```bash
php artisan migrate --path=modules/social-authentication/database/migrations
php artisan db:seed --class=ModusDigital\\SocialAuthentication\\Database\\Seeders\\SocialiteProvidersSeeder
```

This creates:

-   `oauth_providers`: Stores Socialite provider configurations (pre-seeded with Google, GitHub, Facebook)
-   `user_oauth_providers`: Tracks user connections to Socialite providers

## Configuration

### 1. Create OAuth Applications

Create OAuth applications with your providers:

-   **Google**: [Google Cloud Console](https://console.cloud.google.com/)
-   **GitHub**: [GitHub Developer Settings](https://github.com/settings/developers)
-   **Facebook**: [Facebook Developers](https://developers.facebook.com/)

### 2. Configure in Admin Panel

1. Log in to the control panel
2. Navigate to **Authentication > Socialite Providers**
3. Click **Configure** on the provider you want to enable
4. Fill in the credentials:
    - **Client ID**: From your OAuth application
    - **Client Secret**: From your OAuth application (encrypted automatically)
    - **Redirect URI**: Your callback URL (e.g., `https://yourdomain.com/auth/{provider}/callback`)
    - **Enable Provider**: Toggle to enable
5. Save

**Note**: Provider name, type, and sort order are pre-configured and cannot be changed.

## Usage

### Social Login Buttons

When Socialite providers are enabled, social login buttons automatically appear on the login page via the `AUTH_LOGIN_FORM_BEFORE` hook.

### Authentication Flow

1. User clicks "Login with [Provider]" button
2. User is redirected to Socialite provider
3. User authorizes the application
4. User is redirected back to your application
5. The module handles:
    - Finding or creating the user account
    - Linking the Socialite provider to the user
    - Logging the user in

### Routes

The module registers these routes:

-   `GET /auth/{provider}/redirect` - Redirects to Socialite provider
-   `GET /auth/{provider}/callback` - Handles Socialite callback

## Permission

The module adds a new permission:

-   `MANAGE_OAUTH_PROVIDERS` - Allows managing Socialite providers in the admin panel

Grant this permission to users who should configure Socialite providers:

```php
$user->givePermissionTo(Permission::MANAGE_OAUTH_PROVIDERS->value);
```

## Models

### SocialiteProvider

Stores Socialite provider configurations (pre-configured, cannot create/delete).

```php
use ModusDigital\SocialAuthentication\Models\SocialiteProvider;

// Get enabled providers
$providers = SocialiteProvider::enabled()->ordered()->get();

// Configure a provider (credentials only)
$provider = SocialiteProvider::where('provider', 'google')->first();
$provider->update([
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret', // Encrypted automatically
    'redirect_uri' => 'https://yourdomain.com/auth/google/callback',
    'is_enabled' => true,
]);
```

### User Model

The User model now includes a `provider` column to track which Socialite provider the user signed up with.

```php
// Check which provider a user signed up with
$user = User::find($id);
echo $user->provider; // 'google', 'github', 'facebook', or null

// Find users who signed up with Google
$googleUsers = User::where('provider', 'google')->get();

// Check if user signed up with a social provider
$isSocialUser = ! is_null($user->provider);
```

## Supported Providers

Pre-configured providers:

-   **Google** - OAuth 2.0
-   **GitHub** - OAuth 2.0
-   **Facebook** - OAuth 2.0

## Security

-   Client secrets are encrypted using Laravel's encryption
-   OAuth state parameter protects against CSRF attacks
-   Callback URLs are validated against configured redirect URIs
-   Providers are pre-configured - admins can only modify credentials

## Customization

### Adding More Providers

To add more pre-configured providers:

1. Add the provider to `AuthenticationProvider` enum:

```php
// modules/social-authentication/src/Enums/AuthenticationProvider.php
case TWITTER = 'twitter';
```

2. Add labels, icons, and colors for the provider
3. Update the seeder:

```php
// modules/social-authentication/database/seeders/SocialiteProvidersSeeder.php
[
    'name' => 'Twitter',
    'provider' => AuthenticationProvider::TWITTER->value,
    'client_id' => null,
    'client_secret' => null,
    'redirect_uri' => null,
    'is_enabled' => false,
    'sort_order' => 4,
]
```

4. Install the Socialite driver if needed: `composer require socialiteproviders/twitter`
5. Update the blade view with the provider's icon/styling

### Customizing the Login Buttons

Edit the view file:

```
modules/social-authentication/resources/views/livewire/social-auth-buttons.blade.php
```

## Testing

The module includes comprehensive tests:

-   Filament resource configuration (no create/delete, only credential editing)
-   Socialite auth flow (redirect, callback, user creation)
-   Permission-based access control
-   Provider filtering and status checking
-   Pre-seeded providers validation

Run tests:

```bash
php artisan test --filter=SocialiteProviderResourceTest
php artisan test --filter=SocialiteAuthFlowTest
```

## Troubleshooting

### Buttons not appearing on login page

1. Ensure at least one provider is enabled with valid credentials
2. Run `composer dump-autoload`
3. Clear cache: `php artisan optimize:clear`

### Socialite callback errors

1. Verify the redirect URI in your OAuth application matches exactly
2. Check client ID and client secret are correct
3. Ensure the provider is enabled
4. Check Laravel logs for detailed error messages

### Providers not showing in admin panel

1. Run the seeder: `php artisan db:seed --class=ModusDigital\\SocialAuthentication\\Database\\Seeders\\SocialiteProvidersSeeder`
2. Verify migrations have run
3. Check database for `oauth_providers` table

## Architecture

The module follows Laravel's package development best practices:

-   **Service Provider**: Registers routes, views, migrations, and hooks
-   **Filament Plugin**: Auto-discovers resources in the control panel
-   **Livewire Component**: Displays social login buttons
-   **Hook Integration**: Uses the application's hook system
-   **Database-driven**: All configuration in database for runtime flexibility
-   **Pre-configured Providers**: Prevents misconfiguration, admins only manage credentials
-   **Filament v4 Compliant**: Uses proper Filament v4 resource structure with actions
