<?php

namespace App\Jobs;

use App\Http\Helpers\Mgnl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Mgnl $mgnl;
    private array $category;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $category)
    {
        $this->onQueue('PagesQueue');
        $this->category = $category;
        $this->mgnl = new Mgnl();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            echo $this->category['link'];
            $pageCounts = $this->mgnl->getPagesCounts($this->category['link']);
            for($page = 1; $page <= $pageCounts; $page++){
                GetProducts::dispatch($this->category['link'], $page)->delay(now()->addSeconds(10));
            }
        } catch (\Exception $e){
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
        }
    }
}
