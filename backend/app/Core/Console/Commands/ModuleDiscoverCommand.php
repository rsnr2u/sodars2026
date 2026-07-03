<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use App\Core\Registry\ModuleRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class ModuleDiscoverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and register modules from Modules/ and Platform/ directories';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Scanning for modules...');

        $scanPaths = [
            app_path('Modules'),
            app_path('Platform'),
        ];

        $discovered = 0;
        $existing = ModuleRegistry::getModules();

        foreach ($scanPaths as $scanPath) {
            if (!is_dir($scanPath)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()->name('module.json')->in($scanPath)->depth('== 1');

            foreach ($finder as $file) {
                $manifest = json_decode($file->getContents(), true);
                $moduleDir = dirname($file->getRealPath());

                // Look for a Module.php class in this directory
                $moduleClassFile = $moduleDir . '/Module.php';
                if (!file_exists($moduleClassFile)) {
                    $this->warn("  Skipping {$moduleDir}: No Module.php found.");
                    continue;
                }

                // Resolve the fully qualified class name
                $relativePath = str_replace(
                    [app_path() . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
                    ['', '\\'],
                    $moduleDir
                );
                $fqcn = 'App\\' . $relativePath . '\\Module';

                if (!in_array($fqcn, $existing, true)) {
                    $this->line("  <fg=green>Discovered</>: {$fqcn}");
                    $discovered++;
                } else {
                    $name = $manifest['name'] ?? class_basename($fqcn);
                    $this->line("  <fg=cyan>Already registered</>: {$name}");
                }
            }
        }

        $this->newLine();
        $this->info("Discovery complete. Found {$discovered} new module(s).");

        if ($discovered > 0) {
            $this->warn('To register discovered modules, add them to ModuleRegistry::$modules.');
            $this->info('Then run: php artisan module:cache');
        }

        return 0;
    }
}
