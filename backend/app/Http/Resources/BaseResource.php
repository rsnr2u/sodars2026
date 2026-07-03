<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * Customize the resource response wrapper to disable data nesting.
     */
    public static $wrap = null;
}
