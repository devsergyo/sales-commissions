<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class CreateSaleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'exists:sellers,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'sale_date' => ['required', 'date', 'before_or_equal:today']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seller_id.required' => 'O vendedor é obrigatório',
            'seller_id.exists' => 'O vendedor selecionado não existe',
            'amount.required' => 'O valor da venda é obrigatório',
            'amount.numeric' => 'O valor da venda deve ser um número',
            'amount.min' => 'O valor da venda deve ser maior que zero',
            'sale_date.required' => 'A data da venda é obrigatória',
            'sale_date.date' => 'A data da venda deve ser uma data válida',
            'sale_date.before_or_equal' => 'A data da venda não pode ser futura'
        ];
    }
}
