<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use App\Core\Registry\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:status {module : The module class name or short name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show detailed status of a specific module';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $search = $this->argument('module');
        $found = null;

        foreach (ModuleRegistry::getModules() as $moduleClass) {
            $path = ModuleRegistry::getModulePath($moduleClass);
            $manifestPath = $path . '/module.json';

            if (file_exists($manifestPath)) {
                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                $name = $manifest['name'] ?? class_basename($moduleClass);
            } else {
                $name = class_basename($moduleClass);
                $manifest = [];
            }

            if ($name === $search || $moduleClass === $search || class_basename($moduleClass) === $search) {
                $found = [
                    'class' => $moduleClass,
                    'path' => $path,
                    'manifest' => $manifest,
                    'name' => $name,
                ];
                break;
            }
        }

        if (!$found) {
            $this->error("Module '{$search}' not found in registry.");

            return 1;
        }

        $this->info("Module: {$found['name']}");
        $this->newLine();

        $this->table(['Property', 'Value'], [
            ['Class', $found['class']],
            ['Path', $found['path']],
            ['Version', $found['manifest']['version'] ?? '-'],
            ['Enabled', ($found['manifest']['enabled'] ?? true) ? 'Yes' : 'No'],
            ['Description', $found['manifest']['description'] ?? '-'],
        ]);

        // Display providers
        $providers = $found['manifest']['providers'] ?? [];
        if (!empty($providers)) {
            $this->newLine();
            $this->info('Providers:');
            foreach ($providers as $provider) {
                $this->line("  • {$provider}");
            }
        }

        // Display permissions
        $permissions = $found['manifest']['permissions'] ?? [];
        if (!empty($permissions)) {
            $this->newLine();
            $this->info('Permissions:');
            foreach ($permissions as $permission) {
                $this->line("  • {$permission}");
            }
        }

        // Display routes
        $routesPath = $found['path'] . '/Presentation/Routes/v1';
        if (is_dir($routesPath)) {
            $routeFiles = glob($routesPath . '/*.php') ?: [];
            if (!empty($routeFiles)) {
                $this->newLine();
                $this->info('Route Files:');
                foreach ($routeFiles as $routeFile) {
                    $this->line('  • ' . basename($routeFile));
                }
            }
        }

        // Display configs
        $configDir = $found['path'] . '/Infrastructure/Config';
        if (is_dir($configDir)) {
            $configFiles = glob($configDir . '/*.php') ?: [];
            if (!empty($configFiles)) {
                $this->newLine();
                $this->info('Config Files:');
                foreach ($configFiles as $configFile) {
                    $this->line('  • ' . basename($configFile));
                }
            }
        }

        return 0;
    }
}
