<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name_uz' => $this->getTranslation('name', 'uz'),
            'name_ru' => $this->getTranslation('name', 'ru'),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'slug' => $this->slug,
        ];
    }
}
