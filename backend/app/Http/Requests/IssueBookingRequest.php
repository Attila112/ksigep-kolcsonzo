<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'inventory_item_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'inventory_item_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:inventory_items,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'inventory_item_ids.required' =>
                'Legalább egy gépet ki kell választani.',
            'inventory_item_ids.array' =>
                'A kiválasztott gépek formátuma hibás.',
            'inventory_item_ids.min' =>
                'Legalább egy gépet ki kell választani.',
            'inventory_item_ids.*.distinct' =>
                'Ugyanaz a gép csak egyszer választható ki.',
            'inventory_item_ids.*.exists' =>
                'Az egyik kiválasztott gép nem található.',
        ];
    }
}