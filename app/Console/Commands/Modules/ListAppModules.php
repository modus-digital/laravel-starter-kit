<?php

declare(strict_types=1);

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;

final class ListAppModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all modules that are available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $excludeList = [
            'modus-digital/livewire-datatables',
        ];

        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);

        $modulesPath = base_path('app-modules');
        $modules = [];

        if (is_dir($modulesPath)) {
            $directories = scandir($modulesPath);
            foreach ($directories as $directory) {
                if ($directory !== '.' && $directory !== '..' && is_dir($modulesPath.DIRECTORY_SEPARATOR.$directory)) {
                    $modules[] = $directory;
                }
            }
        }

        $allPackages = array_merge(
            array_keys($composerJson['require'] ?? []),
            array_keys($composerJson['require-dev'] ?? [])
        );

        $installedModules = array_values(array_filter($allPackages, function (string $package) use ($excludeList): bool {
            return ! in_array($package, $excludeList, true) && str_starts_with($package, 'modus-digital/');
        }));

        if (empty($modules)) {
            $this->info('No modules found in the modules directory.');

            return Command::SUCCESS;
        }

        $this->info('Available modules:');

        // Get installed package names without the vendor prefix
        $installedModuleNames = array_map(function (string $package): string {
            return str_replace('modus-digital/', '', $package);
        }, $installedModules);

        foreach ($modules as $module) {
            $isInstalled = in_array($module, $installedModuleNames, true);
            $line = "  - {$module}";
            if ($isInstalled) {
                $line .= ' <fg=green>(Installed)</>';
            }
            $this->line($line);
        }

        return Command::SUCCESS;
    }
}
