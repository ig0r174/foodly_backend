<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

class Nuts implements AllergenInterface
{
    private string $name = "nuts";
    private string $ruName = "Орехи";

    public function isContains(ProductResource $product)
    {
        $checkingArray = [
            "фундук",
            "арахис",
            "кешью",
            "грецкий",
            "орех",
            "миндаль",
            "пекан",
            "фисташки",
            "макадамия",
            "кунжут"
        ];

        foreach ($checkingArray as $item){
            if( mb_strpos($product->composition, $item) || mb_strpos($product->name, $item) )
                return true;
        }

        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRussianName(): string
    {
        return $this->ruName;
    }
}
