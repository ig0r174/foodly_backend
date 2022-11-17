<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ProductSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'barcode' => $this->barcode,
            'name' => $this->name,
            'image' => $this->img_local ?? URL::to(sprintf('/api/products/image?url=%s', $this->img_source)),
        ];
    }
}
