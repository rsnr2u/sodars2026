<?php

declare(strict_types=1);

namespace App\Platform\DAM\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Checked via policies inside controller
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // Max 10MB
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'folder_id' => ['nullable', 'string', 'uuid', 'exists:dam_folders,id'],
        ];
    }
}
