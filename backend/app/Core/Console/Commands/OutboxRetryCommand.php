<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OutboxRetryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbox:retry
                            {--id= : Retry a specific dead-letter event by UUID}
                            {--all : Retry all dead-letter events}
                            {--limit=100 : Maximum number of events to retry when using --all}
                            {--dry-run : Preview events to be retried without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry dead-letter outbox events by resetting them to pending status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = $this->option('id');
        $all = $this->option('all');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        if (!$id && !$all) {
            $this->error('Please specify --id=<uuid> or --all to retry dead-letter events.');

            return 1;
        }

        if ($id) {
            return $this->retryById($id, $dryRun);
        }

        return $this->retryAll($limit, $dryRun);
    }

    /**
     * Retry a specific dead-letter event by its UUID.
     */
    protected function retryById(string $id, bool $dryRun): int
    {
        $event = DB::table('outbox_events')
            ->where('id', $id)
            ->where('status', 'dead_letter')
            ->first();

        if (!$event) {
            $this->error("No dead-letter event found with ID: {$id}");

            return 1;
        }

        if ($dryRun) {
            $this->info("[Dry Run] Would retry event: {$event->event_name} ({$event->id})");

            return 0;
        }

        DB::table('outbox_events')
            ->where('id', $id)
            ->update([
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
                'available_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Event {$id} ({$event->event_name}) reset to pending.");

        return 0;
    }

    /**
     * Retry all dead-letter events up to the specified limit.
     */
    protected function retryAll(int $limit, bool $dryRun): int
    {
        $query = DB::table('outbox_events')
            ->where('status', 'dead_letter')
            ->orderBy('created_at', 'asc')
            ->limit($limit);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No dead-letter events found.');

            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry Run] Would retry {$count} dead-letter event(s).");

            // Show a table of events to be retried
            $events = $query->get(['id', 'event_name', 'aggregate_type', 'aggregate_id', 'attempts', 'error_message', 'created_at']);
            $this->table(
                ['ID', 'Event', 'Aggregate', 'Aggregate ID', 'Attempts', 'Error', 'Created'],
                $events->map(fn ($e) => [
                    $e->id,
                    $e->event_name,
                    $e->aggregate_type,
                    $e->aggregate_id,
                    $e->attempts,
                    mb_substr((string) $e->error_message, 0, 50),
                    $e->created_at,
                ])->toArray()
            );

            return 0;
        }

        $ids = $query->pluck('id')->toArray();

        DB::table('outbox_events')
            ->whereIn('id', $ids)
            ->update([
                'status' => 'pending',
                'attempts' => 0,
                'error_message' => null,
                'available_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Reset {$count} dead-letter event(s) to pending.");

        return 0;
    }
}
