<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,upi,rtgs,neft,cheque,card,discount,adjustment',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'nullable|string|max:100',
            'cheque_number' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_date.required' => 'Payment date is required.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method selected.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be greater than 0.',
        ];
    }
}
