<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'source' => 'nullable|string|in:website,phone,walk_in,referral',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'account_id' => 'nullable|uuid|exists:crm_accounts,id',
            'contact_id' => 'nullable|uuid|exists:crm_contacts,id',
        ];
    }
}
