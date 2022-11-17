<?php

namespace App\Http\Resources\Allergens;

use App\Http\Resources\ProductResource;

interface AllergenInterface
{
    public function isContains(ProductResource $product);
    public function getName();
    public function getRussianName();
}
