<?php

declare(strict_types=1);

namespace App\Platform\Settings\Domain\Entities;

use App\Core\Models\BaseModel;

class CompanyProfile extends BaseModel
{
    protected $table = 'company_profiles';

    protected $fillable = [
        'legal_name',
        'tax_number',
        'address_line_1',
        'city',
        'state',
        'zip_code',
        'logo_s3_path',
        'primary_color',
        'secondary_color',
    ];
}
