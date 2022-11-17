<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

class Lactose implements AllergenInterface
{
    private string $name = "lactose";
    private string $ruName = "Лактоза";

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
        if( mb_stristr($product->name, "безлактозн") || mb_stristr($product->name, "без лактоз") )
            return false;

        $namesLactose = [
            "молоко",
            "молочн",
            "творог",
            "торт",
            "суп-пюре",
            "блины",
            "омлет",
        ];

        foreach ($namesLactose as $item){
            if( mb_stristr($product->name, $item) )
                return true;
        }

        $maybeCheckingArray = [
            "пюре",
            "пшенич",
            "пшениц",
            "зерн",
            "маргарин",
            "ветчина",
            "колбаса",
            "соус",
            "заправка",
            "суп",
            "шоколад",
            "мюсли",
            "майонез",
        ];

        foreach ($maybeCheckingArray as $item){
            if( mb_strpos($product->composition, $item) )
                return null;
        }
        return false;

    }
}
