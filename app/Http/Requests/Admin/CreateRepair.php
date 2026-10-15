<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CreateRepair extends FormRequest
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
            'date' => ['required', 'date'],
            'buyer_id' => ['required', 'exists:buyers,id'], // If using buyer_id foreign key
            'product_name' => ['required', 'string', 'max:255'],
            'order_number' => ['required', 'string', 'max:255'],
            'repair_or_sample' => ['required', 'in:repair,sample'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'repair_details' => ['nullable', 'string'],
            'item_delivered_by' => ['required', 'string', 'max:255'],
            'item_received_by' => ['required', 'string', 'max:255'],
            'repair_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ];
    }
}
