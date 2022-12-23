<?php

namespace App\Http\Resources\Supplements;

use App\Http\Resources\ProductResource;
use App\Models\Supplement;

class SupplementsChecker
{

    private ProductResource $product;

    /**
     * @param ProductResource $product
     */
    public function __construct(ProductResource $product)
    {
        $this->product = $product;
    }

    public function inspectSupplements(): array
    {
        return (new SupplementInspect($this->product->composition))->inspectSupplements();
    }
}
