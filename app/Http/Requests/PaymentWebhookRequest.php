<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'        => 'required|exists:orders,id',
            'status'          => 'required|in:success,failed',
            'idempotency_key' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID is required.',
            'order_id.exists'   => 'Order not found.',
            'status.required'   => 'Payment status is required.',
            'status.in'         => 'Payment status must be either success or failed.',
            'idempotency_key.required' => 'Idempotency key is required.',
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
