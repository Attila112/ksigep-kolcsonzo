<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id'),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'price_per_day' => [
                'required',
                'numeric',
                'min:0',
            ],

            'deposit' => [
                'required',
                'numeric',
                'min:0',
            ],

            'active' => [
                'required',
                'boolean',
            ],

            'battery_system_id' => [
                'nullable',
                'integer',
                Rule::exists('battery_systems', 'id'),
            ],

            'required_batteries' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'required_chargers' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}