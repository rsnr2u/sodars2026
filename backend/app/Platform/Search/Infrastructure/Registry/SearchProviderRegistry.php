<?php

declare(strict_types=1);

namespace App\Platform\Search\Infrastructure\Registry;

use App\Platform\Search\Domain\Contracts\SearchProvider;
use InvalidArgumentException;

class SearchProviderRegistry
{
    /**
     * Maps provider keys (e.g. 'mysql') to their class strings.
     *
     * @var array<string, string>
     */
    private array $providers = [];

    /**
     * Register a provider class.
     */
    public function register(string $key, string $providerClass): void
    {
        if (!class_exists($providerClass)) {
            throw new InvalidArgumentException("Search provider class {$providerClass} does not exist.");
        }

        $this->providers[$key] = $providerClass;
    }

    /**
     * Resolve and return a provider instance.
     */
    public function resolve(string $key): SearchProvider
    {
        if (!isset($this->providers[$key])) {
            throw new InvalidArgumentException("No search provider registered for key: {$key}");
        }

        $providerClass = $this->providers[$key];
        return app($providerClass);
    }
}
