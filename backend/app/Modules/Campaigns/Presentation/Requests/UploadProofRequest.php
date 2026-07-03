<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProofRequest extends FormRequest
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
            'inventory_face_id' => ['nullable', 'uuid', 'exists:inventory_faces,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
