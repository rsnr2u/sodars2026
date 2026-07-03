<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Traits\ApiResponse;

abstract class BaseApiController extends BaseController
{
    use ApiResponse;

    /**
     * Standard page limit defaults.
     */
    protected const DEFAULT_PER_PAGE = 15;

    /**
     * Resolve the requested pagination size.
     */
    protected function getPerPage(): int
    {
        $perPage = request()->query('per_page', self::DEFAULT_PER_PAGE);

        return is_numeric($perPage) ? (int) $perPage : self::DEFAULT_PER_PAGE;
    }
}
