<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],
            'customer_email' => [
                'required',
                'email',
                'max:255',
            ],
            'customer_phone' => [
                'required',
                'string',
                'max:30',
            ],

            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'pickup_type' => [
                'required',
                Rule::in([
                    'SELF_PICKUP',
                    'DELIVERY',
                ]),
            ],

            'planned_pickup_at' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('pickup_type') === 'SELF_PICKUP'
                ),
                'nullable',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:end_date',
            ],

            'delivery_postal_code' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('pickup_type') === 'DELIVERY'
                ),
                'nullable',
                'string',
                'max:20',
            ],
            'delivery_city' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('pickup_type') === 'DELIVERY'
                ),
                'nullable',
                'string',
                'max:255',
            ],
            'delivery_street' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('pickup_type') === 'DELIVERY'
                ),
                'nullable',
                'string',
                'max:255',
            ],
            'delivery_house_number' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('pickup_type') === 'DELIVERY'
                ),
                'nullable',
                'string',
                'max:50',
            ],

            'customer_note' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' =>
                'A név megadása kötelező.',
            'customer_email.required' =>
                'Az email-cím megadása kötelező.',
            'customer_email.email' =>
                'Érvényes email-címet adj meg.',
            'customer_phone.required' =>
                'A telefonszám megadása kötelező.',

            'start_date.required' =>
                'A kölcsönzés kezdőnapja kötelező.',
            'start_date.after_or_equal' =>
                'A kölcsönzés nem kezdődhet a múltban.',
            'end_date.required' =>
                'A kölcsönzés zárónapja kötelező.',
            'end_date.after_or_equal' =>
                'A zárónap nem lehet korábbi a kezdőnapnál.',

            'pickup_type.required' =>
                'Az átvétel módját ki kell választani.',
            'pickup_type.in' =>
                'Az átvétel módja érvénytelen.',

            'planned_pickup_at.required' =>
                'Személyes átvételnél add meg a tervezett érkezést.',
            'planned_pickup_at.after_or_equal' =>
                'Az érkezési idő nem lehet a kölcsönzés kezdete előtt.',
            'planned_pickup_at.before_or_equal' =>
                'Az érkezési idő nem lehet a kölcsönzés vége után.',

            'delivery_postal_code.required' =>
                'Házhoz szállításnál az irányítószám kötelező.',
            'delivery_city.required' =>
                'Házhoz szállításnál a település kötelező.',
            'delivery_street.required' =>
                'Házhoz szállításnál az utca kötelező.',
            'delivery_house_number.required' =>
                'Házhoz szállításnál a házszám kötelező.',

            'items.required' =>
                'Legalább egy gépet ki kell választani.',
            'items.array' =>
                'A kiválasztott gépek formátuma hibás.',
            'items.min' =>
                'Legalább egy gépet ki kell választani.',
            'items.*.product_id.exists' =>
                'Az egyik kiválasztott termék nem található.',
            'items.*.product_id.distinct' =>
                'Ugyanaz a termék csak egyszer szerepelhet.',
            'items.*.quantity.min' =>
                'A mennyiség legalább 1 legyen.',
        ];
    }
}