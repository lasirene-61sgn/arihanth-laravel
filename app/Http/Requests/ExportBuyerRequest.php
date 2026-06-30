<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportBuyerRequest extends FormRequest
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
            'format' => 'required|in:excel,pdf',
            'fields' => 'array',
            'fields.*' => 'string|in:bp_code,business_name,name,mobile,email,landline,business_email,refered_by,door_no,shop_no,complex_name,building_name,street_name,area,pincode,city,state,map_location,location_guide,bis_no,gst_no,msme_no,pan_no,tan_no,cin_no,image,aadhar_no,bank_name,account_name,account_no,ifsc_code,branch,bank_city,bank_state,note,created_at,updated_at'
        ];
    }
    
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'format.required' => 'Export format is required.',
            'format.in' => 'Export format must be either excel or pdf.',
            'fields.*.in' => 'Selected field is not valid.'
        ];
    }
}
