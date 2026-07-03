<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Maintenance Commands
|--------------------------------------------------------------------------
|
| These cleanup jobs ensure that transactional outbox, inbox, and
| idempotency records are purged after their retention periods.
|
*/

Schedule::command('outbox:cleanup')->daily()->withoutOverlapping()->description('Cleanup processed outbox events');
Schedule::command('inbox:cleanup')->daily()->withoutOverlapping()->description('Cleanup processed inbox events');
Schedule::command('idempotency:cleanup')->hourly()->withoutOverlapping()->description('Cleanup expired idempotency keys');
