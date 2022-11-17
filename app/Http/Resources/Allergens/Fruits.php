<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

class Fruits implements AllergenInterface
{
    private string $name = "fruits";
    private string $ruName = "Фрукты";

    public function isContains(ProductResource $product)
    {
        $checkingArray = [
            "банан",
            "гранат",
            "ананас",
            "апельсин",
            "мандарин",
            "лимон",
            "хурм",
            "киви",
            "абрикос",
            "персик",
            "яблок",
            "яблоч",
            "груш"
        ];

        foreach ($checkingArray as $item){
            if( mb_strpos($product->composition, $item) )
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
