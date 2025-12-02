<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;

class PaymentService
{
    /**
     * Handle payment provider webhook in an idempotent way.
     *
     * @param  array  $data
     * @return \App\Models\Payment
     *
     * @throws \Exception
     */
    public function handleWebhook(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $orderId        = $data['order_id'];
            $status         = $data['status'];          // "success" or "failed"
            $idempotencyKey = $data['idempotency_key'];

            // Lock the order row so status changes are serialized
            $order = Order::lockForUpdate()->find($orderId);

            if (! $order) {
                throw new Exception('Order not found.');
            }

            try {
                // First webhook for this idempotency key will create the payment
                $payment = Payment::create([
                    'order_id'        => $order->id,
                    'idempotency_key' => $idempotencyKey,
                    'status'          => $status,
                ]);
            } catch (QueryException $e) {
                // Unique constraint violation on idempotency_key => duplicate webhook
                if ($this->isDuplicateKeyException($e)) {
                    return Payment::where('idempotency_key', $idempotencyKey)->firstOrFail();
                }

                throw $e;
            }

            // At this point we know this is the first successful insert for that idempotency key

            if ($status === 'success' && $order->status !== 'paid') {
                $order->update(['status' => 'paid']);
            }

            if ($status === 'failed' && $order->status === 'pending') {
                $order->update(['status' => 'canceled']);

                $hold = $order->hold;

                if ($hold && $hold->used) {
                    $hold->update(['used' => false]);
                }
            }

            return $payment;
        });
    }

    /**
     * Detects a unique constraint violation (duplicate key) for MySQL.
     */
    protected function isDuplicateKeyException(QueryException $e): bool
    {
        $sqlState  = $e->errorInfo[0] ?? null;
        $errorCode = $e->errorInfo[1] ?? null;

        // SQLSTATE 23000, error code 1062 => duplicate entry for unique index in MySQL
        return $sqlState === '23000' && $errorCode === 1062;
    }
}
