<?php

namespace App\Jobs;

use App\Http\Helpers\Mgnl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $link;
    private int $page;
    private Mgnl $mgnl;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $link, int $page)
    {
        $this->onQueue('ProductsQueue');
        $this->link = $link;
        $this->page = $page;
        $this->mgnl = new Mgnl();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            foreach ($this->mgnl->getProductsByCategory($this->link, $this->page) as $productLink) {
                $productData = $this->mgnl->getProductData($productLink);

                if (!empty($productData) && !empty($productData['composition'])) {
                    InsertProduct::dispatch($productData);
                }
            }
        } catch (\Exception $e){
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
        }
    }
}
