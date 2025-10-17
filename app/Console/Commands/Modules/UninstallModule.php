<?php

declare(strict_types=1);

namespace App\Console\Commands\Modules;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class UninstallModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:uninstall {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstalls a module from the modules directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('module');
        $modulePath = base_path("modules/{$module}");

        $this->info("Uninstalling module: {$module} \n\n");
        $this->showRelated($modulePath, $module);

        $shellProcess = new Process(['composer', 'remove', "modus-digital/{$module}"]);
        $shellProcess->setTty(Process::isTtySupported());
        $shellProcess->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $shellProcess->isSuccessful()) {
            $this->error("Failed to uninstall module: {$module}");
            $this->error($shellProcess->getErrorOutput());

            return Command::FAILURE;
        }

        $this->info("Module uninstalled successfully: {$module}");

        return Command::SUCCESS;
    }

    protected function showRelated($modulePath, $module)
    {
        if (! is_dir($modulePath)) {
            $this->error("Module '{$module}' does not exist in the modules directory.");
            $this->line('');
        }

        $this->info("Module '{$module}' exists in the modules directory.");
        $this->line('');
    }
}
