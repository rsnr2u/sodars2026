<?php

declare(strict_types=1);

namespace App\Modules\CRM;

use App\Core\Registry\ModuleRegistry;

class Module
{
    public static function getName(): string
    {
        return 'CRM';
    }

    public static function getPath(): string
    {
        return __DIR__;
    }
}
