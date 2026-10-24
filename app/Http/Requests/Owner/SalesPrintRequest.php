<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class SalesPrintRequest extends FormRequest
{
    /**
     * Access is decided by the route's can:view_sale middleware; sales are scoped to the user's businesses in the controller.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * The same filters as the sales list screen.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'business' => ['nullable', 'string', 'max:36'],
            'sale_status' => ['nullable', 'in:completed,pending,canceled'],
            'payment_status' => ['nullable', 'in:paid,unpaid,partial'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sale_status.in' => 'Choose a valid sale status.',
            'payment_status.in' => 'Choose a valid payment status.',
            'to.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
