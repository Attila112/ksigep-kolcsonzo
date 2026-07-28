<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('reviews', 'product_id')
                    ->where(fn($query) => $query->where(
                        'user_id',
                        $this->user()->id
                    )),
            ],
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'comment' => [
                'required',
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
            'product_id.required' => 'A termék megadása kötelező.',
            'product_id.exists' => 'A kiválasztott termék nem található.',
            'product_id.unique' => 'Ezt a terméket már értékelted.',
            'rating.required' => 'Az értékelés megadása kötelező.',
            'rating.integer' => 'Az értékelésnek egész számnak kell lennie.',
            'rating.between' => 'Az értékelés 1 és 5 közötti lehet.',
            'comment.required' => 'A vélemény megadása kötelező.',
            'comment.max' => 'A vélemény legfeljebb 5000 karakter lehet.',
            'title.max' => 'A cím legfeljebb 255 karakter lehet.',
        ];
    }
}
