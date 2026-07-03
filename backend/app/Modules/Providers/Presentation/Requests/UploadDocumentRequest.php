<?php

declare(strict_types=1);

namespace App\Modules\Providers\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
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
            'document_type' => ['required', 'string', 'in:tax_certificate,business_registry,screen_ownership_proof'],
            'file_path' => ['required', 'string'],
        ];
    }
}
