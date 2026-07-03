<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCreativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'file_path' => ['required', 'string', 'max:500'],
            'file_name' => ['sometimes', 'string', 'max:150'],
            'file_type' => ['sometimes', 'string', 'in:jpg,jpeg,png,pdf,ai,psd,cdr,zip,mp4,JPG,PNG,PDF,AI,PSD,CDR,ZIP,MP4'],
            'file_size_bytes' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
