<?php

namespace App\Jobs;

use App\Http\Helpers\Sbermarket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetSbermarketProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $shopLink;
    private string $categoryLink;
    private int $page;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    //public function __construct(Sbermarket $sbermarket, $shopLink, $categoryLink, $page = 1)
    public function __construct($shopLink, $categoryLink, $page = 1)
    {
        $this->onQueue('SbermarketProductsQueue');
        $this->shopLink = $shopLink;
        $this->categoryLink = $categoryLink;
        $this->page = $page;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $products = (new Sbermarket(false))->getProducts($this->shopLink, $this->categoryLink, $this->page);
        echo "<pre>" . print_r($products, true) . "</pre>";
    }
}
