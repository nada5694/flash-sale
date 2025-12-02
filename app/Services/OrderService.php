<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Hold;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function createOrder(int $holdId): Order
    {
        return DB::transaction(function () use ($holdId) {

            $hold = Hold::lockForUpdate()->find($holdId);

            if (!$hold) {
                throw new Exception('Hold not found.');
            }

            if ($hold->used) {
                throw new Exception('This hold has already been used.');
            }

            if ($hold->expires_at->isPast()) {
                throw new Exception('This hold has expired.');
            }

            // Create order
            $order = Order::create([
                'hold_id' => $hold->id,
                'status' => 'pending',
            ]);

            // Mark hold as used
            $hold->update(['used' => true]);

            return $order;
        });
    }
}
