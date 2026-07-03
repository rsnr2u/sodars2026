<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Registry\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $cachePath = base_path('bootstrap/cache/modules.php');

        if (file_exists($cachePath)) {
            $cache = require $cachePath;

            // 1. Register Service Providers
            foreach ($cache['providers'] ?? [] as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }

            // 2. Load configurations
            foreach ($cache['configs'] ?? [] as $key => $config) {
                config()->set($key, $config);
            }
        } else {
            // Load dynamically using ModuleRegistry
            foreach (ModuleRegistry::getModules() as $moduleClass) {
                $path = ModuleRegistry::getModulePath($moduleClass);
                $manifestPath = $path . '/module.json';

                if (file_exists($manifestPath)) {
                    $manifest = json_decode((string) file_get_contents($manifestPath), true);

                    if (isset($manifest['enabled']) && $manifest['enabled'] === true) {
                        // Register providers
                        foreach ($manifest['providers'] ?? [] as $provider) {
                            if (class_exists($provider)) {
                                $this->app->register($provider);
                            }
                        }

                        // Automatically load config files under Infrastructure/Config/
                        $configDir = $path . '/Infrastructure/Config';
                        if (is_dir($configDir)) {
                            foreach (glob($configDir . '/*.php') ?: [] as $configFile) {
                                $configKey = strtolower(basename($configFile, '.php'));
                                config()->set($configKey, require $configFile);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
