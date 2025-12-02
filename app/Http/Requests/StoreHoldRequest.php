<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreHoldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists'   => 'Product does not exist.',
            'qty.required'        => 'Quantity is required.',
            'qty.integer'         => 'Quantity must be an integer.',
            'qty.min'             => 'Quantity must be at least 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
