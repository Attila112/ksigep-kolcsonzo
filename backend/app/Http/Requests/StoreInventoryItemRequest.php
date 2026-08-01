<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'inventory_code' => [
                'required',
                'string',
                'max:100',
                'unique:inventory_items,inventory_code',
            ],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:inventory_items,serial_number',
            ],
            'status' => [
                'required',
                Rule::in([
                    'AVAILABLE',
                    'RENTED',
                    'MAINTENANCE',
                    'DAMAGED',
                    'INACTIVE',
                ]),
            ],
            'admin_note' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'A kiválasztott termék nem található.',
            'inventory_code.unique' => 'Ez a belső gépkód már használatban van.',
            'serial_number.unique' => 'Ez a sorozatszám már használatban van.',
            'status.in' => 'A megadott gépállapot érvénytelen.',
        ];
    }
}
