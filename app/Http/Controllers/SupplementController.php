<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Dobavkam;
use App\Jobs\GetSupplementData;
use App\Models\Supplement;
use Illuminate\Http\Request;

class SupplementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        foreach ((new Dobavkam)->getCollection() as $link){
            GetSupplementData::dispatch($link)->delay(2);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $supplement = Supplement::create($request->all());

        return response()->json([
            'status' => true,
            'supplement' => $supplement
        ]);
    }
}
