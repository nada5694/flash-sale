<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'payment_id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'idempotency_key' => $this->idempotency_key,
            'created_at' => $this->created_at,
        ];
    }
}
