<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:products,id'
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Product ID is required.',
            'id.exists' => 'Product not found.'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    /**
     * Override failedValidation to return consistent error response
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
