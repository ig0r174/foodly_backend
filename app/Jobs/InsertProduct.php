<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class InsertProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $productData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productData)
    {
        $this->onQueue('InsertQueue');
        $this->productData = $productData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $requestN = Request::create('/api/products', 'POST', $this->productData);
            $response = app()->handle($requestN);
            var_dump($this->productData);
        } catch (\Exception $e){
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
        }
    }
}
