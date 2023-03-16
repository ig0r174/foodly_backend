<?php

use App\Http\Controllers\ParserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplementController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SupplementResource;
use App\Jobs\GetProductDataFromSbermarket;
use App\Jobs\GetProductDataFromSearcher;
use App\Models\History;
use App\Models\Intolerance;
use App\Models\Product;
use App\Models\Rskrf;
use App\Models\RskrfInfo;
use App\Models\RskrfResearch;
use App\Models\Supplement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/products/image', function () {

    if (isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
        try {
            $localPath = sprintf("%s.jpg", md5($_GET['url']));
            if (file_exists($localPath)) {
                $img = imagecreatefromjpeg($localPath);
            } else {
                $img = imagecreatefromjpeg(urldecode($_GET['url']));
                Storage::disk('public_uploads')->put($localPath, file_get_contents($_GET['url']));
                Product::where('img_source', $_GET['url'])->update([
                    "img_local" => URL::to(sprintf("/uploads/%s", $localPath))
                ]);
            }
            header("Content-Type: image/jpg");
            imagejpeg($img);
        } catch (Exception $e) {
            header("Content-Type: image/png");
            $img = imageCreateFromPng("../public/uploads/no_photo.png");
            imageAlphaBlending($img, true);
            imageSaveAlpha($img, true);
            imagepng($img);
        }

        imagedestroy($img);
        exit;
    }
});

Route::apiResource('parser', ParserController::class);
Route::post('rskrf', function () {
    $rskrf = Rskrf::create([
        "name" => \Request::input("name"),
        "link" => \Request::input("link"),
        "rating" => \Request::input("rating"),
        "is_intruder" => \Request::input("is_intruder"),
        "barcode" => \Request::input("barcode"),
        "research_date" => !empty(\Request::input("research_date")) ? \Request::input("research_date") . "-01-01" : null
    ]);

    foreach (\Request::input("info") as $item) {
        RskrfInfo::create([
            "rskrf_id" => $rskrf->getKey(),
            "type" => $item['type'],
            "value" => $item['value']
        ]);
    }

    foreach (\Request::input("research") as $item) {
        RskrfResearch::create([
            "rskrf_id" => $rskrf->getKey(),
            "name" => $item['name'],
            "value" => $item['value']
        ]);
    }
});

Route::post('user', UserController::class);

Route::get('products/search', [ProductController::class, 'search']);
Route::get('products/{barcode}', function ($barcode) {

    $productRedis = Redis::get($barcode);

    if( empty($productRedis) ) {
        $product = Product::where('barcode', $barcode);
        $total = $product->count();

        if ($total == 0) {
            Redis::set($barcode, "searcher");
            GetProductDataFromSearcher::dispatch($barcode);
            return response()->json(['message' => 'Product Not Found, loading was started'], 202);
        };

        $collection = ProductResource::collection($product->get());
        Redis::set($barcode, json_encode($collection), 'EX', 3600);
        return $collection;
    } else if( $productRedis == "searcher" ){
        return response()->json(['message' => 'We are loading data from internet'], 202);
    } else if( $productRedis == "not_found" ){
        return response()->json(['message' => 'Product not found anywhere'], 404);
    } else return response()->json(["data" => json_decode($productRedis), "source" => "redis"]);

})->where(['barcode' => '\d+']);
Route::apiResource('products', ProductController::class);

Route::get('supplements/{id}', function ($id) {
    $supplement = Supplement::where('id', $id);

    if ($supplement->count() == 0) {
        return response()->json(['message' => 'Supplement Not Found'], 404);
    }

    return SupplementResource::collection($supplement->get());
})->where(['id' => '\d+']);
Route::apiResource('supplements', SupplementController::class);
Route::get('intolerances', function () {
    return response()->json(['items' => Intolerance::select('name', 'alias')->get()]);
});
