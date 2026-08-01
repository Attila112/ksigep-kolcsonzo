<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectBookingRequest extends FormRequest
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
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Az elutasítás indoklása kötelező.',
            'reason.string' => 'Az elutasítás indoklása szöveg legyen.',
            'reason.max' => 'Az elutasítás indoklása legfeljebb 2000 karakter lehet.',
        ];
    }
}