<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class HoldService
{
    /**
     * Create a hold on a product
     *
     * @param int $productId
     * @param int $qty
     * @return Hold
     * @throws Exception
     */
    public function createHold(int $productId, int $qty): Hold
    {
        return DB::transaction(function () use ($productId, $qty) {

            $product = Product::where('id', $productId)
                        ->lockForUpdate()
                        ->firstOrFail();

            $available = $product->stock - $product->holds()->where('used', false)
                                                            ->where('expires_at', '>', Carbon::now())
                                                            ->sum('qty');

            if ($available < $qty) {
                throw new Exception("Not enough stock available.");
            }

            $hold = Hold::create([
                'product_id' => $productId,
                'qty' => $qty,
                'expires_at' => Carbon::now()->addMinutes(2),
                'used' => false,
            ]);

            return $hold;
        });
    }
}
