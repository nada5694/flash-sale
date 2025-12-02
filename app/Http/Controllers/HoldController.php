<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHoldRequest;
use App\Http\Resources\HoldResource;
use App\Services\HoldService;
use Illuminate\Http\JsonResponse;
use Exception;

class HoldController extends Controller
{
    protected HoldService $holdService;

    public function __construct(HoldService $holdService)
    {
        $this->holdService = $holdService;
    }

    public function store(StoreHoldRequest $request): JsonResponse
    {
        try {
            $hold = $this->holdService->createHold(
                $request->product_id,
                $request->qty
            );

            return response()->json([
                'success' => true,
                'data' => new HoldResource($hold)
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
