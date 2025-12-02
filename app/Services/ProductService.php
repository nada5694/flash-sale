<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * Get product with basic fields, cached for a short period.
     *
     * The "available" stock is calculated in ProductResource based on holds,
     * so it always reflects the latest state and is not cached here.
     */
    public function getProduct(int $id): Product
    {
        $cacheKey = "product:{$id}";

        return Cache::remember($cacheKey, now()->addSeconds(10), function () use ($id) {
            return Product::findOrFail($id);
        });
    }

    /**
     * Invalidate the cached product entry.
     */
    public function forgetProductCache(int $id): void
    {
        $cacheKey = "product:{$id}";
        Cache::forget($cacheKey);
    }
}
