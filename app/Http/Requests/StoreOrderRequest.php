<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hold_id' => 'required|exists:holds,id',
        ];
    }

    public function messages(): array
    {
        return [
            'hold_id.required' => 'Hold ID is required.',
            'hold_id.exists'   => 'Hold does not exist.',
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
