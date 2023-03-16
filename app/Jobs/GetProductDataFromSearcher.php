<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Request;

class GetProductDataFromSearcher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private int $barcode;
    public int $uniqueFor = 180;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($barcode)
    {
        $this->onQueue('SearcherQueue');
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
            $productData = json_decode(file_get_contents("http://nginx:4000/?barcode=" . $this->barcode), true);

            if( empty($productData))
                Redis::set($this->barcode, "not_found", 'EX', 3600);

            $requestN = Request::create('/api/products', 'POST', $productData);
            $response = app()->handle($requestN);
        } catch (\Exception $e){
            Redis::set($this->barcode, "not_found", 'EX', 3600);
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()]);
        }
    }

    public function uniqueId()
    {
        return $this->barcode;
    }
}
