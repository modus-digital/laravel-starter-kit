<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Activity $resource
 */
final class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'log_name' => $this->resource->log_name,
            'description' => $this->resource->getTranslatedDescription(),
            'event' => $this->resource->event,
            'subject_type' => $this->resource->subject_type,
            'subject_id' => $this->resource->subject_id,
            'causer_type' => $this->resource->causer_type,
            'causer_id' => $this->resource->causer_id,
            'causer' => $this->when(
                $this->resource->causer !== null,
                fn (): array => [
                    'id' => $this->resource->causer?->id,
                    'name' => $this->resource->causer?->name,
                    'email' => $this->resource->causer?->email,
                ]
            ),
            'properties' => $this->resource->properties,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
