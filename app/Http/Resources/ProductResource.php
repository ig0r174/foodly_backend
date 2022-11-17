<?php

namespace App\Http\Resources;

use App\Http\Resources\Allergens\AllergenChecker;
use App\Http\Resources\Supplements\SupplementsChecker;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->composition = $this->normalizeComposition($this->composition);
        return [
            'barcode' => $this->barcode,
            'name' => $this->name,
            'composition' => $this->composition,
            'image' => $this->img_local ?? URL::to(sprintf('/api/products/image?url=%s', $this->img_source)),
            'supplements' => (new SupplementsChecker($this))->inspectSupplements(),
            'allergens' => (new AllergenChecker($this))->inspectAllergens(),
        ];
    }

    private function normalizeComposition(string $composition): string
    {
        $composition = mb_strtolower($composition);
        $composition = preg_replace('/([^е])(е|e|у)(\s|)(\d+)/', '${1}E${4}', $composition);
        $composition = trim(preg_replace('/\s+/', ' ', $composition));
        $composition = preg_replace('/(.),(\S)/', '${1}, ${2}', $composition);
        $composition = preg_replace('/(\S)[(](\S)/', '${1} (${2}', $composition);

        if( $exploded = explode(".", $composition) ){
            if( count($exploded) > 1 )
                $composition = $exploded[0];
        }

        return $composition;
    }
}
