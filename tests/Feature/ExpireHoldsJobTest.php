<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Hold;
use App\Jobs\ExpireHoldsJob;
use Carbon\Carbon;

class ExpireHoldsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_holds_job_marks_expired_holds_as_used(): void
    {
        $product = Product::create([
            'name'  => 'Flash Sale Product',
            'price' => 100,
            'stock' => 10,
        ]);

        $expiredHold = Hold::create([
            'product_id' => $product->id,
            'qty'        => 3,
            'used'       => false,
            'expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $activeHold = Hold::create([
            'product_id' => $product->id,
            'qty'        => 2,
            'used'       => false,
            'expires_at' => Carbon::now()->addMinutes(2),
        ]);

        // First run
        (new ExpireHoldsJob())->handle();

        $this->assertTrue($expiredHold->fresh()->used);
        $this->assertFalse($activeHold->fresh()->used);

        // Second run (idempotent)
        (new ExpireHoldsJob())->handle();

        $this->assertTrue($expiredHold->fresh()->used);
        $this->assertFalse($activeHold->fresh()->used);
    }
}
