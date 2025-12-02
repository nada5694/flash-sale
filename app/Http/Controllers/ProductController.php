<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductShowRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function show(ProductShowRequest $request, $id)
    {
        $product = $this->productService->getProduct($id);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }
}
