<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryStatusRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in([
                    'AVAILABLE',
                    'INSPECTION',
                    'MAINTENANCE',
                    'DAMAGED',
                    'INACTIVE',
                ]),
            ],
            'admin_note' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('status') !== 'AVAILABLE'
                ),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' =>
                'A gép állapotának megadása kötelező.',
            'status.in' =>
                'A megadott gépállapot érvénytelen.',
            'admin_note.required' =>
                'Ehhez az állapothoz admin megjegyzés szükséges.',
            'admin_note.string' =>
                'Az admin megjegyzésnek szövegnek kell lennie.',
            'admin_note.max' =>
                'Az admin megjegyzés legfeljebb 5000 karakter lehet.',
        ];
    }
}