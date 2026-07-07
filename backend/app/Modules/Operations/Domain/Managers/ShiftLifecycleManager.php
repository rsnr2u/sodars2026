<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Managers;

use App\Modules\Operations\Domain\Entities\Shift;
use App\Modules\Operations\Domain\Events\ShiftStarted;
use App\Modules\Operations\Domain\Events\ShiftCompleted;
use Illuminate\Support\Str;

class ShiftLifecycleManager
{
    public function create(array $data): Shift
    {
        $shift = Shift::create(array_merge($data, [
            'id' => (string) Str::uuid(),
            'status' => $data['status'] ?? 'active',
        ]));

        return $shift;
    }

    public function startShift(Shift $shift): void
    {
        event(new ShiftStarted($shift->id, 1, $shift->toArray()));
    }

    public function endShift(Shift $shift): void
    {
        event(new ShiftCompleted($shift->id, 1, $shift->toArray()));
    }
}
