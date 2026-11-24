<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Courier
 */
class CourierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'vehicle_type' => $this->vehicle_type->value,
            'vehicle_plate' => $this->vehicle_plate,
            'level' => $this->level,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
