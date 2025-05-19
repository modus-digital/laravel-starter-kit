<h2 align="center" style="font-weight: bold"><a href="https://modus-digital.com" target="_blank" style="vertical-align: middle;"><img src="https://www.modus-digital.com/images/uploads/logos/modus-logo-totaal-wit.png"></a> - Laravel starter kit</h2>

<p align="center">
<a href="https://github.com/modus-digital/laravel-starter-kit"><img src="https://img.shields.io/github/v/release/modus-digital/laravel-starter-kit" alt="Latest Stable Version"></a>
<a href="https://github.com/modus-digital/laravel-starter-kit"><img src="https://img.shields.io/badge/license-MIT-green" alt="License"></a>
<a href="https://herd.laravel.com/new?starter-kit=modus-digital/laravel-starter-kit" target="_blank" rel="noopener noreferrer"><img src="https://img.shields.io/badge/Install%20with%20Herd-f55247?logo=laravel&logoColor=white"></a>
</p>

---

A comprehensive Laravel starter kit by Modus Digital for quickly bootstrapping new Laravel applications with built-in features and best practices.

## Features
This starter kit has a couple default useful functionalities to easily get started with a new project. The following features are included in this starter kit:

- Build on **Laravel 12** and **Livewire**
- **Filament Admin panel** intergration
    - Managing Users
    - Translation manager
    - Backup manager
    - RBAC manager
    - Health statistics
    - Mail intergration
- File-based routing using **Laravel Folio**
- **Livewire Volt** intergration
- Two factor authentication using any Authenticator app


## Requirements

- PHP 8.3 or higher
- Composer
- Node.js & (P}NPM (PNPM Prefered)

## Installation

### installing pnpm
```bash
npm install --global corepack@latest && corepack enable pnpm    # Using corepack
npm install --global pnpm@@latest-10                            # Using npm directly
```

### Option 1: Create a new project using the laravel installer

#### Installing the laravel installer and create a new project
```bash
composer global require laravel/installer

laravel new  <project name> --using=modus-digital/laravel-starter-kit
cd <project name>
```

### Option 2: Clone the repository

```bash
git clone https://github.com/modus-digital/laravel-starter-kit.git your-project-name
cd your-project-name
composer install
pnpm install
```

### Option 3: Use the Laravel Herd installer UI

---

### Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database (or configure your preferred database in .env)
touch database/database.sqlite

# Run migrations
php artisan migrate --seed
```

## Development

Start the development server with a single command:


### Option 1: Using composer
```bash
composer dev
```

This command starts:
- Laravel development server
- Queue worker
- Vite for front-end assets

### Option 2: Using laravel herd
Add The site to Laravel Herd and run Vite
```bash
pnpm run dev
```

## Testing

Run the test suite:

```bash
composer test
```

## Code Quality Tools

The starter kit includes several code quality tools:

- **PHP CS Fixer** for code style
- **Laravel Pint** for Laravel-specific style
- **Larastan** for static analysis
- **Rector** for automated refactoring

## License

MIT

## Credits

Created and maintained by [Modus Digital](https://modus-digital.com).
