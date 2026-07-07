<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Enums;

enum FirmwareInstallationStatus: string
{
    case Draft = 'Draft';
    case Published = 'Published';
    case Scheduled = 'Scheduled';
    case Downloading = 'Downloading';
    case Installing = 'Installing';
    case Installed = 'Installed';
    case Rollback = 'Rollback';
    case Failed = 'Failed';
}
