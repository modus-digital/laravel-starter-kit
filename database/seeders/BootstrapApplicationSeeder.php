<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Outerweb\Settings\Models\Setting;

final class BootstrapApplicationSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * -----------------------------------------------------------------------
         * Bootstrap Application Settings
         * This seeder is used to bootstrap the application settings.
         * -----------------------------------------------------------------------
         * This settings are used to bootstrap the application settings.
         * If you need to change the settings, you can change them in the database.
         * The settings are stored in the database table 'settings'.
         *
         * @see https://github.com/outerweb/laravel-settings
         */
        $settings = [
            // Branding
            ['key' => 'branding.primary_color', 'value' => null],
            ['key' => 'branding.secondary_color', 'value' => null],
            ['key' => 'branding.font', 'value' => null],
            ['key' => 'branding.logo_light', 'value' => null],
            ['key' => 'branding.logo_dark', 'value' => null],
            ['key' => 'branding.emblem_light', 'value' => null],
            ['key' => 'branding.emblem_dark', 'value' => null],
            ['key' => 'branding.app_name', 'value' => config('app.name')],
            ['key' => 'branding.tagline', 'value' => null],

            // Mailgun Signing Key
            ['key' => 'integrations.mailgun.webhook_signing_key', 'value' => null],

            // OAuth
            ['key' => 'integrations.oauth.google.enabled', 'value' => config('modules.socialite.providers.google')],
            ['key' => 'integrations.oauth.google.client_id', 'value' => null],
            ['key' => 'integrations.oauth.google.client_secret', 'value' => null],

            ['key' => 'integrations.oauth.github.enabled', 'value' => config('modules.socialite.providers.github')],
            ['key' => 'integrations.oauth.github.client_id', 'value' => null],
            ['key' => 'integrations.oauth.github.client_secret', 'value' => null],

            ['key' => 'integrations.oauth.microsoft.enabled', 'value' => config('modules.socialite.providers.microsoft')],
            ['key' => 'integrations.oauth.microsoft.client_id', 'value' => null],
            ['key' => 'integrations.oauth.microsoft.client_secret', 'value' => null],

            // S3
            ['key' => 'integrations.s3.enabled', 'value' => false],
            ['key' => 'integrations.s3.key', 'value' => null],
            ['key' => 'integrations.s3.secret', 'value' => null],
            ['key' => 'integrations.s3.region', 'value' => null],
            ['key' => 'integrations.s3.bucket', 'value' => null],
            ['key' => 'integrations.s3.endpoint', 'value' => null],
            ['key' => 'integrations.s3.use_path_style_endpoint', 'value' => false],
        ];

        foreach ($settings as $row) {
            Setting::create($row);
        }

        /**
         * ----------------------------------------------------------------------------------
         * Bootstrap Task Statuses
         * ----------------------------------------------------------------------------------
         * This seeder is used to bootstrap the task statuses if the tasks module is enabled.
         * ----------------------------------------------------------------------------------
         * The task statuses are used to bootstrap the task statuses.
         * If you need to change the task statuses, you can change them in the database.
         * The task statuses are stored in the database table 'task_statuses'.
         */
        if (config('modules.tasks.enabled')) {
            TaskStatus::findOrCreateByName('Todo', '#fde047');
            TaskStatus::findOrCreateByName('In Progress', '#2563eb');
            TaskStatus::findOrCreateByName('Done', '#65a30d');
        }

        /**
         * ----------------------------------------------------------------------------------
         * Bootstrap Default roles and permissions
         * ----------------------------------------------------------------------------------
         * This seeder is used to bootstrap the default roles and permissions.
         * ----------------------------------------------------------------------------------
         * The default roles and permissions are used to bootstrap the default roles and permissions.
         * If you need to change the default roles and permissions, you can change them in the database.
         * The default roles and permissions are stored in the database table 'roles' and 'permissions'.
         */
        // Sync permissions from enum
        foreach (PermissionEnum::cases() as $permission) {
            if (! $permission->shouldSync()) {
                continue;
            }

            Permission::firstOrCreate(
                ['name' => $permission->value, 'guard_name' => 'web']
            );
        }

        // Create internal system roles (bypass global scope)
        $superAdminRole = Role::withInternal()->firstOrCreate(
            ['name' => Role::SUPER_ADMIN, 'guard_name' => 'web'],
            ['internal' => true]
        );
        $superAdminRole->update(['internal' => true]);

        $adminRole = Role::withInternal()->firstOrCreate(
            ['name' => Role::ADMIN, 'guard_name' => 'web'],
            ['internal' => true]
        );
        $adminRole->update(['internal' => true]);

        // Assign permissions to Super Admin (all permissions including internal ones)
        $superAdminRole->syncPermissions(Permission::all());

        // Assign permissions to Admin (all permissions except super-admin-only ones)
        $adminPermissions = collect(PermissionEnum::cases())
            ->filter(fn (PermissionEnum $permission) => ! $permission->isInternalOnly() || $permission === PermissionEnum::AccessControlPanel || $permission === PermissionEnum::ImpersonateUsers)
            ->map(fn (PermissionEnum $permission) => $permission->value)
            ->all();

        $adminRole->syncPermissions($adminPermissions);

        /**
         * ----------------------------------------------------------------------------------
         * Bootstrap Default users
         * ----------------------------------------------------------------------------------
         * This seeder is used to bootstrap the default users.
         * ----------------------------------------------------------------------------------
         * The default users are used to bootstrap the default users.
         * If you need to change the default users, you can change them in the database.
         * The default users are stored in the database table 'users'.
         */
        $user = User::create([
            'name' => 'Modus Admin',
            'email' => 'admin@modus-digital.com',
            'password' => Hash::make('W8chtW00rd01!'),
        ]);

        $user->assignRole(Role::SUPER_ADMIN);
    }
}
