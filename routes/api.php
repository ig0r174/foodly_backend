<?php

use App\Http\Controllers\ParserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplementController;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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

    if( isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL) ){
        $localPath = sprintf("%s.jpg", md5($_GET['url']));
        if( file_exists($localPath) ){
            $img = imagecreatefromjpeg($localPath);
        } else {
            $img = imagecreatefromjpeg($_GET['url']);
            Storage::disk('public_uploads')->put($localPath, file_get_contents($_GET['url']));
            Product::where('img_source', $_GET['url'])->update([
                "img_local" => URL::to(sprintf("/uploads/%s", $localPath))
            ]);
        }

        header("Content-Type: image/jpg");
        imagejpeg($img);
        imagedestroy($img);
    }
});
Route::get('products/{barcode}', function ($barcode) {
    return ProductResource::collection(Product::where('barcode', $barcode)->get());
})->where(['barcode' => '\d+']);
Route::get('products/search', [ProductController::class, 'search']);
Route::apiResource('products', ProductController::class);
Route::apiResource('parser', ParserController::class);
Route::apiResource('supplements', SupplementController::class);
