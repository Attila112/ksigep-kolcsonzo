<?php

namespace App\Http\Requests\Admin;

use App\Models\BatteryItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatteryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    BatteryItem::STATUS_AVAILABLE,
                    BatteryItem::STATUS_INSPECTION,
                    BatteryItem::STATUS_MAINTENANCE,
                    BatteryItem::STATUS_DAMAGED,
                    BatteryItem::STATUS_INACTIVE,
                ]),
            ],

            'admin_note' => [
                Rule::requiredIf(
                    fn () =>
                        $this->input('status') !==
                        BatteryItem::STATUS_AVAILABLE
                ),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' =>
                'Az állapot megadása kötelező.',

            'status.in' =>
                'A kiválasztott állapot nem állítható be kézzel.',

            'admin_note.required' =>
                'Ehhez az állapothoz admin megjegyzés megadása kötelező.',

            'admin_note.string' =>
                'Az admin megjegyzésnek szövegnek kell lennie.',

            'admin_note.max' =>
                'Az admin megjegyzés legfeljebb 5000 karakter lehet.',
        ];
    }
}