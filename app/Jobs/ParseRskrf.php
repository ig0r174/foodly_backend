<?php

namespace App\Jobs;

use App\Http\Helpers\Rskrf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpFoundation\Request;

class ParseRskrf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $link;
    /**
     * @var false|mixed
     */
    private $isItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($link, $isItem = false)
    {
        $this->onQueue('RskrfQueue');
        $this->link = $link;
        $this->isItem = $isItem;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rskrf = new Rskrf();
        $data = !$this->isItem ? $rskrf->getItems($this->link) : $rskrf->getItemData($this->link);
        if( !empty($data['barcode']) ){
            try {
                $requestN = Request::create('/api/rskrf', 'POST', $data);
                $response = app()->handle($requestN);
                echo json_encode($data);
            } catch (\Exception $e){
                var_dump([$e->getMessage(), $e->getFile(), $e->getLine()]);
            }
        } else {
            foreach ($data as $item){
                ParseRskrf::dispatch($item, true);
            }
        }
    }
}
