<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Mgnl;
use App\Http\Helpers\Rskrf;
use App\Http\Helpers\Sbermarket;
use App\Jobs\CalculateRating;
use App\Jobs\GetPages;
use App\Jobs\GetSbermarketProducts;
use App\Jobs\ParseRskrf;
use App\Models\Product;
use Illuminate\Http\Request;

class ParserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $mgnl = new Mgnl;
        if (!empty($request->get('productLink'))) {
            return response()->json([
                'status' => true,
                'productData' => $mgnl->getProductData($request->get('productLink'))
            ]);
        } else if (!empty($request->get('categoryLink'))) {
            $products = $mgnl->getProductsByCategory($request->get('categoryLink'));
            return response()->json([
                'status' => true,
                'items' => $products,
                'total' => count($products)
            ]);
        } else if( !empty($request->get('rskrf')) ){
            foreach ((new Rskrf)->getCategories() as $category){
                ParseRskrf::dispatch($category);
            }
        } else if( !empty($request->get('rating')) ){
            CalculateRating::dispatch(Product::where('id', 2159)->first());
        } else {
            foreach ($mgnl->getCategories() as $category){
                GetPages::dispatch($category)->delay(now()->addSeconds(5));
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
