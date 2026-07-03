<?php

declare(strict_types=1);

namespace App\Modules\Branches\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCoverageAreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'country_id' => ['required', 'uuid', 'exists:countries,id'],
            'state_id' => ['required', 'uuid', 'exists:states,id'],
            'district_id' => ['nullable', 'uuid', 'exists:districts,id'],
            'city_id' => ['required', 'uuid', 'exists:cities,id'],
        ];
    }
}
