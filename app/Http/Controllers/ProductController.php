<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductSearchResource;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $products = Product::all();

        return response()->json([
            'status' => true,
            'products' => $products
        ]);
    }

    public function search(Request $request)
    {
        $querySearch = $request->query("q");
        if (!empty($request->query("q"))) {
            $barcode = intval($querySearch);
            $items = $barcode > 0 ? Product::where(['barcode' => intval($querySearch)])->orWhere('barcode', 'like', $barcode . "%")
                : Product::where('name', 'like', '%' . $querySearch . '%');

            return ProductSearchResource::collection($items->get());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->all());

        return response()->json([
            'status' => true,
            'product' => $product
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     * @return Response
     */
    public function edit(Product $product)
    {
        //
    }


    /**
     * @param Request $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());

        return response()->json([
            'status' => true,
            'message' => "Product updated successfully",
            'post' => $product
        ]);
    }
}
