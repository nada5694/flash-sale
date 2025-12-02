<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Hold;
use App\Services\HoldService;
use Carbon\Carbon;
use Exception;

class HoldServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HoldService $holdService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->holdService = $this->app->make(HoldService::class);
    }

    public function test_cannot_create_holds_beyond_available_stock(): void
    {
        $product = Product::create([
            'name'  => 'Flash Sale Product',
            'price' => 100,
            'stock' => 5,
        ]);

        // This should succeed: 3 units
        $this->holdService->createHold($product->id, 3);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not enough stock available.');

        // This should fail because only 2 units are left
        $this->holdService->createHold($product->id, 3);
    }

    public function test_expired_holds_do_not_reduce_available_stock(): void
    {
        $product = Product::create([
            'name'  => 'Flash Sale Product',
            'price' => 100,
            'stock' => 5,
        ]);

        // Create an expired hold
        Hold::create([
            'product_id' => $product->id,
            'qty'        => 5,
            'used'       => false,
            'expires_at' => Carbon::now()->subMinutes(1),
        ]);

        // Because the hold is expired, this should still succeed
        $hold = $this->holdService->createHold($product->id, 5);

        $this->assertEquals(5, $hold->qty);
    }
}
