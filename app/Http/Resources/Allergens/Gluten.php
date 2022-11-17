<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

class Gluten implements AllergenInterface
{
    private string $name = "gluten";
    private string $ruName = "Глютен";

    public function getName(): string
    {
        return $this->name;
    }

    public function getRussianName(): string
    {
        return $this->ruName;
    }

    public function isContains(ProductResource $product)
    {
        $checkingArray = [
            "пшенич",
            "Камут",
            "Манная крупа",
            "Спельта",
            "Кус-кус",
            "Булгур",
            "Пшениц",
            "Столовый уксус",
            "уксус столовый",
            "содержит глютен"
        ];

        foreach ($checkingArray as $item){
            if( mb_strpos($product->composition, $item) )
                return true;
        }

        return false;
    }
}
