<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => $this->completed,
            'priority' => $this->priority?->value,
            'priority_label' => $this->priority?->label(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'is_overdue' => $this->is_overdue,
            'completed_at' => $this->completed_at?->toISOString(),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'color' => $this->category->color,
                ];
            }),
            'total_time_seconds' => $this->total_time_seconds ?? 0,
            'formatted_total_time' => $this->formatted_total_time,
            'timer_started_at' => $this->timer_started_at?->toIso8601String(),
            'is_timer_running' => $this->is_timer_running,
            'time_entries' => TimeEntryResource::collection($this->whenLoaded('timeEntries')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
