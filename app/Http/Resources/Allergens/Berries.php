<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

class Berries implements AllergenInterface
{

    private string $name = "berries";
    private string $ruName = "Ягоды";

    public function isContains(ProductResource $product)
    {
        $namesBerries = [
            "малин",
            "клубник",
            "земляник",
            "черная смородин",
            "клюкв",
            "брусни",
            "черник",
            "вишн",
            "шиповник"
        ];

        foreach ($namesBerries as $item){
            if( mb_stristr($product->name, $item) || mb_stristr($product->composition, $item) )
                return true;
        }

        $maybeCheckingArray = [
            "белая черешн",
            "белой черешн",
            "желтая черешн",
            "желтой черешн",
            "белая смородин",
            "белой смородин",
            "крыжовник",
            "чернослив",
            "арбуз"
        ];

        foreach ($maybeCheckingArray as $item){
            if( mb_stristr($product->name, $item) || mb_stristr($product->composition, $item) )
                return null;
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
