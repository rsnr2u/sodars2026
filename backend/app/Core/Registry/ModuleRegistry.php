<?php

declare(strict_types=1);

namespace App\Core\Registry;

use ReflectionClass;
use RuntimeException;

class ModuleRegistry
{
    /**
     * List of registered module class names.
     *
     * @var array<int, string>
     */
    private static array $modules = [
        \App\Platform\Settings\Module::class,
        \App\Platform\Shared\Module::class,
        \App\Modules\Branches\Module::class,
        \App\Modules\Providers\Module::class,
        \App\Modules\Inventory\Module::class,
        \App\Modules\Campaigns\Module::class,
        \App\Modules\Bookings\Module::class,
        \App\Modules\Finance\Module::class,
        \App\Modules\Wallet\Module::class,
        \App\Modules\CRM\Module::class,
        \App\Platform\DAM\Module::class,
        \App\Platform\Notifications\Module::class,
        \App\Platform\Workflows\Module::class,
        \App\Platform\Automation\Module::class,
        \App\Platform\Search\Module::class,
    ];

    /**
     * Register a module class.
     */
    public static function register(string $moduleClass): void
    {
        if (!class_exists($moduleClass)) {
            throw new RuntimeException("Module class does not exist: {$moduleClass}");
        }

        if (!in_array($moduleClass, self::$modules, true)) {
            self::$modules[] = $moduleClass;
        }
    }

    /**
     * Get all registered modules.
     *
     * @return array<int, string>
     */
    public static function getModules(): array
    {
        return self::$modules;
    }

    /**
     * Get absolute path to the module folder.
     */
    public static function getModulePath(string $moduleClass): string
    {
        $reflector = new ReflectionClass($moduleClass);
        $fileName = $reflector->getFileName();

        if (!$fileName) {
            throw new RuntimeException("Unable to resolve file path for module: {$moduleClass}");
        }

        return dirname($fileName);
    }

    /**
     * Clear the registry (useful for tests).
     */
    public static function clear(): void
    {
        self::$modules = [];
    }
}
