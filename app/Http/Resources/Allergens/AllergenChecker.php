<?php

namespace App\Http\Resources\Allergens;
use App\Http\Resources\ProductResource;

class AllergenChecker
{
    private ProductResource $product;
    private array $allergensClasses = [
        Gluten::class,
        Lactose::class,
        Fruits::class,
        Nuts::class,
        Berries::class
    ];

    public function __construct(ProductResource $product)
    {
        $this->product = $product;
    }

    public function inspectAllergens(): array
    {
        $result = [];
        foreach($this->allergensClasses as $class){
            $allergenClass = (new $class);
            $result[] = [
                "name" => $allergenClass->getName(),
                "ruName" => $allergenClass->getRussianName(),
                "status" => $allergenClass->isContains($this->product)
            ];
        }

        return $result;
    }

}
