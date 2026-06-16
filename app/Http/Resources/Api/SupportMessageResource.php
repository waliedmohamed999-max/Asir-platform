<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sender_type' => $this->sender_type,
            'sender_name' => $this->sender?->name,
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
