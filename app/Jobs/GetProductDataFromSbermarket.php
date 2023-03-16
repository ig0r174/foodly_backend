<?php

namespace App\Jobs;

use App\Http\Helpers\Sbermarket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Request;

class GetProductDataFromSbermarket implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $barcode;
    public int $uniqueFor = 180;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $barcode)
    {
        $this->onQueue('SbermarketQueue');
        $this->barcode = $barcode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
//            $sbermarket = new Sbermarket;
//            $productData = $sbermarket->getProductData($this->barcode);

            $productData = json_decode(file_get_contents("http://nginx:3000/" . $this->barcode), true);
            $requestN = Request::create('/api/products', 'POST', $productData);
            $response = app()->handle($requestN);
        } catch (\Exception $e){
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()]);
        }
    }

    public function uniqueId()
    {
        return $this->barcode;
    }
}
