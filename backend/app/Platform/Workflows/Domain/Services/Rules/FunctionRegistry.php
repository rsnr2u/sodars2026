<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Domain\Services\Rules;

use RuntimeException;

class FunctionRegistry
{
    protected array $functions = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Register a new custom function.
     */
    public function register(string $name, callable $callback): void
    {
        $this->functions[strtolower($name)] = $callback;
    }

    /**
     * Call a registered function with arguments.
     */
    public function call(string $name, array $args): mixed
    {
        $lowerName = strtolower($name);
        if (!isset($this->functions[$lowerName])) {
            throw new RuntimeException("Rules engine function is not registered: {$name}");
        }

        return call_user_func_array($this->functions[$lowerName], $args);
    }

    /**
     * Register default rule functions.
     */
    protected function registerDefaults(): void
    {
        $this->register('contains', function (mixed $haystack, mixed $needle): bool {
            if (is_array($haystack)) {
                return in_array($needle, $haystack, true);
            }
            if (is_string($haystack)) {
                return str_contains($haystack, (string)$needle);
            }
            return false;
        });

        $this->register('is_weekend', function (): bool {
            $day = date('N');
            return $day === '6' || $day === '7';
        });

        $this->register('user_has_role', function (string $role): bool {
            $user = auth()->user();
            return $user !== null && $user->hasRole($role);
        });
    }
}
