<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];

        if ($this->relationLoaded('orders')) {
            $data['orders_count'] = $this->orders->count();
        }

        if (isset($this->role)) {
            $data['role'] = $this->role;
        }

        if (isset($this->can_edit)) {
            $data['can_edit'] = $this->can_edit;
        }

        return $data;
    }
}
