<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Setting
 */
class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'timezone' => $this->timezone,
            'currency' => $this->whenLoaded(
                'currency',
                fn() => new CurrencyResource($this->currency)
            ),
        ];
    }
}
