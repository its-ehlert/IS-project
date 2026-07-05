<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'neighborhoodId' => $this->neighborhood_id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'read' => (bool) $this->is_read,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
