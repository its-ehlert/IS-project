<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
            'userName' => $this->user->name,
            'neighborhoodId' => $this->neighborhood_id,
            'neighborhood' => $this->neighborhood->name,
            'status' => $this->status,
            'notes' => $this->notes,
            'reportedAt' => $this->reported_at?->toIso8601String(),
            'verified' => (bool) $this->verified,
        ];
    }
}
