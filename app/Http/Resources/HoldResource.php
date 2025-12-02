<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HoldResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'hold_id' => $this->id,
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'expires_at' => $this->expires_at,
        ];
    }
}
