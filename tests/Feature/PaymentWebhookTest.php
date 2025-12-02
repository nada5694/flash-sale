<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = $this->app->make(PaymentService::class);
    }

    protected function createOrderWithHold(string $orderStatus = 'pending', bool $holdUsed = true): Order
    {
        $product = Product::create([
            'name'  => 'Flash Sale Product',
            'price' => 100,
            'stock' => 10,
        ]);

        $hold = Hold::create([
            'product_id' => $product->id,
            'qty'        => 2,
            'used'       => $holdUsed,
            'expires_at' => Carbon::now()->addMinutes(2),
        ]);

        return Order::create([
            'hold_id' => $hold->id,
            'status'  => $orderStatus,
        ]);
    }

    public function test_success_webhook_creates_payment_and_marks_order_paid(): void
    {
        $order = $this->createOrderWithHold('pending', true);

        $data = [
            'order_id'        => $order->id,
            'status'          => 'success',
            'idempotency_key' => 'payment-123',
        ];

        $payment = $this->paymentService->handleWebhook($data);

        $this->assertInstanceOf(Payment::class, $payment);

        $this->assertDatabaseHas('payments', [
            'id'               => $payment->id,
            'order_id'         => $order->id,
            'idempotency_key'  => 'payment-123',
            'status'           => 'success',
        ]);

        $this->assertEquals('paid', $order->fresh()->status);
    }

    public function test_failed_webhook_cancels_order_and_releases_hold(): void
    {
        $order = $this->createOrderWithHold('pending', true);

        $data = [
            'order_id'        => $order->id,
            'status'          => 'failed',
            'idempotency_key' => 'payment-456',
        ];

        $payment = $this->paymentService->handleWebhook($data);

        $this->assertDatabaseHas('payments', [
            'id'               => $payment->id,
            'order_id'         => $order->id,
            'idempotency_key'  => 'payment-456',
            'status'           => 'failed',
        ]);

        $order->refresh();
        $hold = $order->hold->fresh();

        $this->assertEquals('canceled', $order->status);
        $this->assertFalse($hold->used);
    }

    public function test_webhook_is_idempotent_for_same_idempotency_key(): void
    {
        $order = $this->createOrderWithHold('pending', true);

        $data = [
            'order_id'        => $order->id,
            'status'          => 'success',
            'idempotency_key' => 'payment-789',
        ];

        $paymentFirst = $this->paymentService->handleWebhook($data);
        $paymentSecond = $this->paymentService->handleWebhook($data);

        $this->assertEquals($paymentFirst->id, $paymentSecond->id);

        $this->assertEquals(
            1,
            Payment::where('idempotency_key', 'payment-789')->count()
        );

        $this->assertEquals('paid', $order->fresh()->status);
    }
}
