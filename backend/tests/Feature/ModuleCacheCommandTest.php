<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Registry\ModuleRegistry;
use Tests\Core\FeatureTestCase;

class ModuleCacheCommandTest extends FeatureTestCase
{
    /**
     * Test module:cache command generates the cache file.
     */
    public function test_module_cache_command_creates_cache_file(): void
    {
        $cachePath = base_path('bootstrap/cache/modules.php');

        // Ensure no stale cache exists
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }

        $this->artisan('module:cache')
            ->expectsOutputToContain('Compiling modules configuration')
            ->expectsOutputToContain('cached successfully')
            ->assertExitCode(0);

        $this->assertFileExists($cachePath);

        // Cleanup
        if (file_exists($cachePath)) {
            unlink($cachePath);
        }
    }

    /**
     * Test module:clear command removes the cache file.
     */
    public function test_module_clear_command_removes_cache_file(): void
    {
        $cachePath = base_path('bootstrap/cache/modules.php');

        // Create a dummy cache file
        file_put_contents($cachePath, '<?php return [];');

        $this->artisan('module:clear')
            ->expectsOutputToContain('Module cache cleared')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($cachePath);
    }

    /**
     * Test module:clear when no cache exists.
     */
    public function test_module_clear_command_warns_when_no_cache(): void
    {
        $cachePath = base_path('bootstrap/cache/modules.php');

        if (file_exists($cachePath)) {
            unlink($cachePath);
        }

        $this->artisan('module:clear')
            ->expectsOutputToContain('No module cache file found')
            ->assertExitCode(0);
    }

    /**
     * Test module:list command displays registered modules.
     */
    public function test_module_list_command_displays_modules(): void
    {
        $this->artisan('module:list')
            ->assertExitCode(0);
    }

    /**
     * Test module:discover command scans directories.
     */
    public function test_module_discover_command_runs(): void
    {
        $this->artisan('module:discover')
            ->expectsOutputToContain('Scanning for modules')
            ->expectsOutputToContain('Discovery complete')
            ->assertExitCode(0);
    }
}
