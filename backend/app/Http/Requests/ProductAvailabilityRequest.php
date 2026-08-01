<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductAvailabilityRequest extends FormRequest
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
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' =>
            'A kezdődátum megadása kötelező.',
            'start_date.date' =>
            'A kezdődátum formátuma érvénytelen.',
            'end_date.required' =>
            'A záródátum megadása kötelező.',
            'end_date.date' =>
            'A záródátum formátuma érvénytelen.',
            'end_date.after_or_equal' =>
            'A záródátum nem lehet korábbi a kezdődátumnál.',
        ];
    }
}
