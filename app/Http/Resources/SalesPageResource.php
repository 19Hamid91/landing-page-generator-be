<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'product_name'        => $this->product_name,
            'product_description' => $this->product_description,
            'target_audience'     => $this->target_audience,
            'price'               => $this->price,
            'features'            => $this->features,
            'usp'                 => $this->usp,
            'ai_output'           => $this->ai_output,
            'template_name'       => $this->template_name,
            'images'              => array_map(function($path) {
                return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
            }, $this->images ?? []),
            'language'            => $this->language,
            'currency'            => $this->currency,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
