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

            'battery_allocations' => [
                'sometimes',
                'array',
            ],

            'battery_allocations.*.inventory_item_id' => [
                'required',
                'integer',
                'distinct',
                'exists:inventory_items,id',
            ],

            'battery_allocations.*.battery_item_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'battery_allocations.*.battery_item_ids.*' => [
                'required',
                'integer',
                'exists:battery_items,id',
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
            'battery_allocations.array' =>
            'Az akkumulátor-hozzárendelések formátuma hibás.',
            'battery_allocations.*.inventory_item_id.required' =>
            'Meg kell adni, melyik géphez tartozik az akkumulátor-hozzárendelés.',
            'battery_allocations.*.inventory_item_id.distinct' =>
            'Egy géphez csak egy akkumulátor-hozzárendelési blokk adható meg.',
            'battery_allocations.*.inventory_item_id.exists' =>
            'Az akkumulátor-hozzárendeléshez megadott gép nem található.',
            'battery_allocations.*.battery_item_ids.required' =>
            'Legalább egy akkumulátort vagy töltőt ki kell választani.',
            'battery_allocations.*.battery_item_ids.array' =>
            'A kiválasztott akkumulátorok és töltők formátuma hibás.',
            'battery_allocations.*.battery_item_ids.min' =>
            'Legalább egy akkumulátort vagy töltőt ki kell választani.',
            'az akkumulátor vagy töltő csak egyszer választható ki egy géphez.',
            'battery_allocations.*.battery_item_ids.*.exists' =>
            'Az egyik kiválasztott akkumulátor vagy töltő nem található.',
        ];
    }
}
