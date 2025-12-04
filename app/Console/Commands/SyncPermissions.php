<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RBAC\Permission as PermissionEnum;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

final class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions from the Permission enum to the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Syncing permissions from enum to database...');
        $this->newLine();

        $synced = 0;
        $skipped = 0;
        $moduleSkipped = 0;

        foreach (PermissionEnum::cases() as $permission) {
            // Check if permission should be synced based on module config
            if (! $permission->shouldSync()) {
                $this->line("  <fg=yellow>⊘</> {$permission->value} (module disabled)");
                $moduleSkipped++;

                continue;
            }

            // Check if permission already exists
            $exists = Permission::where('name', $permission->value)
                ->where('guard_name', 'web')
                ->exists();

            if ($exists) {
                $this->line("  <fg=gray>−</> {$permission->value} (already exists)");
                $skipped++;

                continue;
            }

            // Create the permission
            Permission::create([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);

            $this->line("  <fg=green>✓</> {$permission->value}");
            $synced++;
        }

        $this->newLine();
        $this->info('Sync complete!');
        $this->line("  Created: <fg=green>{$synced}</>");
        $this->line("  Skipped: <fg=gray>{$skipped}</>");

        if ($moduleSkipped > 0) {
            $this->line("  Module-disabled: <fg=yellow>{$moduleSkipped}</>");
        }

        return self::SUCCESS;
    }
}
