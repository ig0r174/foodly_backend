<?php

namespace App\Jobs;

use App\Http\Resources\ProductResource;
use App\Http\Resources\Supplements\SupplementInspect;
use App\Http\Resources\Supplements\SupplementsChecker;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateRating implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Product $product;
    private int $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Product $product)
    {
        $this->onQueue('RatingQueue');
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rating = 5;
        foreach ((new SupplementInspect($this->product->composition))->inspectSupplements() as $supplement){
            $rating -= $supplement['level'] == 0 ? 0 : $supplement['level'] * 0.1;
        }

        $this->product->rating = $rating;
        $this->product->save();
    }
}
