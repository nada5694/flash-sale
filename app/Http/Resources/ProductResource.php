<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $activeHeldQty = $this->holds()
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->sum('qty');

        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'price'     => $this->price,
            'stock'     => $this->stock,
            'available' => max($this->stock - $activeHeldQty, 0),
        ];
    }
}
