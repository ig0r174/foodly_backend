<?php

namespace App\Jobs;

use App\Http\Helpers\Dobavkam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Request;

class GetSupplementData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $link;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $link)
    {
        $this->onQueue("SupplementsQueue");
        $this->link = $link;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = (new Dobavkam)->getSupplementData($this->link);
            $request = Request::create('/api/supplements', 'POST', $data);
            sleep(1);
            $response = app()->handle($request);
        } catch (\Exception $e){
            var_dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
        }
    }
}
