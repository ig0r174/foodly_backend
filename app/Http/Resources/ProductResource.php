<?php

namespace App\Http\Resources;

use App\Http\Resources\Allergens\AllergenChecker;
use App\Http\Resources\Supplements\SupplementsChecker;
use App\Models\Rskrf;
use App\Models\RskrfInfo;
use App\Models\RskrfResearch;
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
        $result = [
            'barcode' => $this->barcode,
            'name' => $this->name,
            'composition' => $this->composition,
            'image' => $this->img_local ?? URL::to(sprintf('/api/products/image?url=%s', $this->img_source)),
            'isPerishable' => $this->isPerishable($this->name),
            'supplements' => (new SupplementsChecker($this))->inspectSupplements(),
            'allergens' => (new AllergenChecker($this))->inspectAllergens(),
            'rskrf' => $this->getRskrf($this->barcode)
        ];

        if( !empty($request->input('profiles')) ){
            $intolerances = [];
            $allergens = array_column($result['allergens'], 'status', 'name');
            foreach ($request->input('profiles') as $i => $profile){
                $intolerances[$i] = [
                    "name" => $profile['name'],
                    "status" => true
                ];

                foreach ($profile['params'] as $param){
                    if($allergens[$param]) {
                        $intolerances[$i]['status'] = false;
                        break;
                    } else if( is_null($allergens[$param]) ){
                        $intolerances[$i]['status'] = null;
                        break;
                    }
                }
            }

            $result['intolerances'] = $intolerances;
        }

        return $result;
    }

    public function normalizeComposition(string $composition): string
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

    private function isPerishable($productName): bool
    {
        $perishableNames = [
            "мясо",
            "птица",
            "птицы",
            "дичь",
            "субпродукт",
            "колбас",
            "сосиск",
            "сардельк",
            "рыба",
            "сыр",
            "молоко",
            "молочн",
            "кефир",
            "ряженка",
            "снежок"
        ];

        foreach ($perishableNames as $perishableName){
            if( mb_stristr($productName, $perishableName) ) return true;
        }

        return false;
    }

    private function getRskrf($barcode)
    {
        $rskrf = Rskrf::where('barcode', $barcode);

        if( $rskrf->count() == 0 )
            return null;

        $rskrf = $rskrf->first();

        return [
            "link" => $rskrf->link,
            "rating" => $rskrf->rating,
            "is_intruder" => $rskrf->rating == 0,
            "research_date" => empty($rskrf->research_date) ? null : intval(mb_substr($rskrf->research_date, 0, 4)),
            "info" => RskrfInfo::select('type','value')->where('rskrf_id', $rskrf->id)->get(),
            "research" => RskrfResearch::select('name','value')->where('rskrf_id', $rskrf->id)->get()
        ];

    }
}
