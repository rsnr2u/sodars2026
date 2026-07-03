<?php

declare(strict_types=1);

namespace App\Modules\CRM\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opportunity_id' => 'nullable|uuid|exists:crm_opportunities,id',
            'account_id' => 'required|uuid|exists:crm_accounts,id',
            'quotation_number' => 'required|string|unique:crm_quotations,quotation_number',
            'valid_until' => 'required|date|after:today',
            'subtotal_cents' => 'required|integer|min:0',
            'discount_cents' => 'nullable|integer|min:0',
            'tax_cents' => 'nullable|integer|min:0',
            'grand_total_cents' => 'required|integer|min:0',
            'currency' => 'nullable|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.inventory_face_id' => 'required|uuid|exists:inventory_faces,id',
            'items.*.start_date' => 'required|date|after_or_equal:today',
            'items.*.end_date' => 'required|date|after_or_equal:items.*.start_date',
            'items.*.daily_frequency' => 'nullable|integer|min:1',
            'items.*.price_cents' => 'required|integer|min:0',
        ];
    }
}
