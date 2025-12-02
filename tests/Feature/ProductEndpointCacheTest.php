<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Hold;
use Carbon\Carbon;

class ProductEndpointCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_endpoint_uses_cache_without_stale_available_stock(): void
    {
        $product = Product::create([
            'name'  => 'Flash Sale Product',
            'price' => 100,
            'stock' => 10,
        ]);

        // First request warms the cache
        $response1 = $this->getJson('/api/products/' . $product->id);
        $response1->assertOk();
        $this->assertEquals(10, $response1->json('data.available'));

        // Create a hold that should reduce availability to 8
        Hold::create([
            'product_id' => $product->id,
            'qty'        => 2,
            'used'       => false,
            'expires_at' => Carbon::now()->addMinutes(2),
        ]);

        // Second request should still read Product from cache,
        // but "available" must reflect the new hold from the database.
        $response2 = $this->getJson('/api/products/' . $product->id);
        $response2->assertOk();
        $this->assertEquals(8, $response2->json('data.available'));
    }
}
