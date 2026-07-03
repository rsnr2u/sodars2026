<?php

declare(strict_types=1);

namespace App\Observers;

use App\Core\Models\BaseModel;
use Illuminate\Support\Facades\Event;

class BaseObserver
{
    /**
     * Handle the BaseModel "created" event.
     */
    public function created(BaseModel $model): void
    {
        // Auto audit or events trigger
    }

    /**
     * Handle the BaseModel "updated" event.
     */
    public function updated(BaseModel $model): void
    {
        // Auto audit or events trigger
    }

    /**
     * Handle the BaseModel "deleted" event.
     */
    public function deleted(BaseModel $model): void
    {
        // Auto audit or events trigger
    }
}
