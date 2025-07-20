<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsigneeRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'consignee_name' => 'required|string|max:255',
            'gstin' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
            'address' => 'required|string|max:500',
            'address2' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:10',
            'status' => 'required|in:active,inactive'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'consignee_name.required' => 'Consignee name is required.',
            'consignee_name.max' => 'Consignee name cannot exceed 255 characters.',
            'gstin.regex' => 'Please enter a valid GSTIN format (e.g., 22AAAAA0000A1Z5).',
            'gstin.max' => 'GSTIN cannot exceed 15 characters.',
            'address.required' => 'Address is required.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'address2.max' => 'Address 2 cannot exceed 500 characters.',
            'city.required' => 'City is required.',
            'city.max' => 'City name cannot exceed 100 characters.',
            'state.required' => 'State is required.',
            'state.max' => 'State name cannot exceed 100 characters.',
            'zip.required' => 'ZIP code is required.',
            'zip.max' => 'ZIP code cannot exceed 10 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'consignee_name' => 'consignee name',
            'gstin' => 'GSTIN',
            'address' => 'address',
            'address2' => 'address 2',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'ZIP code',
            'status' => 'status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert GSTIN to uppercase if provided
        if ($this->gstin) {
            $this->merge([
                'gstin' => strtoupper($this->gstin),
            ]);
        }

        // Capitalize city and state names
        if ($this->city) {
            $this->merge([
                'city' => ucwords(strtolower($this->city)),
            ]);
        }

        if ($this->state) {
            $this->merge([
                'state' => ucwords(strtolower($this->state)),
            ]);
        }

        // Trim consignee name
        if ($this->consignee_name) {
            $this->merge([
                'consignee_name' => trim($this->consignee_name),
            ]);
        }
    }
}
