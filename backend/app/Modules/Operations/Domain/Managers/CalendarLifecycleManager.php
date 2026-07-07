<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Managers;

use App\Modules\Operations\Domain\Entities\BusinessCalendar;
use App\Modules\Operations\Domain\Events\CalendarUpdated;
use Illuminate\Support\Str;

class CalendarLifecycleManager
{
    public function create(array $data): BusinessCalendar
    {
        $calendar = BusinessCalendar::create(array_merge($data, [
            'id' => (string) Str::uuid(),
        ]));

        return $calendar;
    }

    public function updateCalendar(BusinessCalendar $calendar, array $data): void
    {
        $calendar->update($data);
        event(new CalendarUpdated($calendar->id, 1, $calendar->toArray()));
    }
}
