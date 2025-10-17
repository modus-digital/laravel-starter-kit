<?php

declare(strict_types=1);

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class InstallAppModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:install {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs a module from the modules directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');
        $modulePath = base_path("modules/{$module}");

        $this->info("Installing module: {$module} \n\n");
        $this->showRelated($modulePath, $module);

        $shellProcess = new Process(['composer', 'require', "modus-digital/{$module}"]);
        $shellProcess->setTty(Process::isTtySupported());
        $shellProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $shellProcess->isSuccessful()) {
            $this->error("Failed to install module: {$module}");
            $this->error($shellProcess->getErrorOutput());

            return Command::FAILURE;
        }

        $this->info("Module installed successfully: {$module}");

        return Command::SUCCESS;
    }

    protected function showRelated($modulePath, $module)
    {
        if (! is_dir($modulePath)) {
            $this->error("Module '{$module}' does not exist in the modules directory.");
            $this->line('');

            $modulesDir = base_path('modules');
            $modules = is_dir($modulesDir) ? array_filter(scandir($modulesDir), function ($item) use ($modulesDir) {
                return $item !== '.' && $item !== '..' && is_dir($modulesDir.DIRECTORY_SEPARATOR.$item);
            }) : [];

            if (! empty($modules)) {
                // Find similar modules
                $similar = collect($modules)
                    ->filter(function ($name) use ($module) {
                        return str_contains(mb_strtolower($name), mb_strtolower($module));
                    })->values()->all();

                if (! empty($similar)) {
                    $this->warn('Did you mean one of these modules?');
                    foreach ($similar as $name) {
                        $this->line("  - {$name}");
                    }
                } else {
                    $this->info('Available modules:');
                    foreach ($modules as $name) {
                        $this->line("  - {$name}");
                    }
                }
            } else {
                $this->info('No modules found in the modules directory.');
            }

            return Command::FAILURE;
        }
    }
}
