<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentWebhookRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Exception;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function webhook(PaymentWebhookRequest $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->handleWebhook($request->validated());

            return response()->json([
                'success' => true,
                'data' => new PaymentResource($payment)
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
