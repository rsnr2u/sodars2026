<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use App\Core\Registry\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleListCommand extends Command
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
    protected $description = 'List all registered modules and their status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modules = ModuleRegistry::getModules();

        if (empty($modules)) {
            $this->warn('No modules registered.');

            return 0;
        }

        $rows = [];

        foreach ($modules as $moduleClass) {
            $path = ModuleRegistry::getModulePath($moduleClass);
            $manifestPath = $path . '/module.json';

            if (file_exists($manifestPath)) {
                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                $rows[] = [
                    $manifest['name'] ?? class_basename($moduleClass),
                    $manifest['version'] ?? '1.0.0',
                    ($manifest['enabled'] ?? true) ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>',
                    $moduleClass,
                    $path,
                ];
            } else {
                $rows[] = [
                    class_basename($moduleClass),
                    '-',
                    '<fg=yellow>No Manifest</>',
                    $moduleClass,
                    $path,
                ];
            }
        }

        $this->table(
            ['Name', 'Version', 'Status', 'Class', 'Path'],
            $rows
        );

        return 0;
    }
}
