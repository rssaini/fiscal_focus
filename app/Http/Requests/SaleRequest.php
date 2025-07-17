<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'ref_party_id' => 'nullable|exists:parties,id',
            'vehicle_no' => 'required|string|max:20',
            'tare_wt' => 'required|numeric|min:0',
            'gross_wt' => 'required|numeric|min:0|gt:tare_wt',
            'product_id' => 'required|exists:products,id',
            'product_rate' => 'nullable|numeric|min:0',
            'tp_no' => 'nullable|string|max:50',
            'invoice_rate' => 'nullable|numeric|min:0',
            'tp_wt' => 'nullable|numeric|min:0',
            'rec_no' => 'required|string|max:50',
            'royalty_book_no' => 'nullable|string|max:50',
            'royalty_receipt_no' => 'nullable|string|max:50',
            'consignee_name' => 'required|string|max:255',
            'consignee_address' => 'required|string',
            'notes' => 'nullable|string',
            'date' => 'nullable|date',
        ];

        // For updates, make invoice_number unique except for current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['invoice_number'] = 'required|string|unique:sales,invoice_number,' . $this->route('sale');
        } else {
            $rules['invoice_number'] = 'nullable|string|unique:sales,invoice_number';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'vehicle_no.required' => 'Vehicle number is required.',
            'tare_wt.required' => 'Tare weight is required.',
            'gross_wt.required' => 'Gross weight is required.',
            'gross_wt.gt' => 'Gross weight must be greater than tare weight.',
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'Selected product does not exist.',
            'rec_no.required' => 'Receipt number is required.',
            'consignee_name.required' => 'Consignee name is required.',
            'consignee_address.required' => 'Consignee address is required.',
        ];
    }
}
