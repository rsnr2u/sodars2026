<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;

class ModuleClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the compiled module cache file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cachePath = base_path('bootstrap/cache/modules.php');

        if (file_exists($cachePath)) {
            unlink($cachePath);
            $this->info('Module cache cleared successfully.');
        } else {
            $this->warn('No module cache file found to clear.');
        }

        return 0;
    }
}
