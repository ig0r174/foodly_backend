<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductSearchResource;
use App\Models\History;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
        $page = intval($request->query("page")) > 0 ? intval($request->query("page")) : 1;
        $limit = 20;

        if (!empty($request->query("q"))) {
            $barcode = intval($querySearch);
            $items = $barcode > 0 ? Product::where(['barcode' => intval($querySearch)])->orWhere('barcode', 'like', $barcode . "%")
                : Product::where('name', 'like', '%' . $querySearch . '%');
            $total = $items->count();
            $items->selectRaw('*, POSITION(? IN name) AS position', [$querySearch]);
            $itemsCollection = ProductSearchResource::collection($items->offset(($page - 1) * $limit)->limit($limit)->orderBy('position')->get());

            return response()->json([
                "total" => $total,
                "isLastPage" => (count($itemsCollection) < $limit || $page * $limit >= $total),
                "items" => $itemsCollection
            ], 200, ['Content-Type => application/json']);
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

    public function history(Request $request)
    {
        $page = intval($request->query("page")) > 0 ? intval($request->query("page")) : 1;
        $limit = 20;

        $items = DB::table('history')
            ->select(['barcode', 'name', 'img_local', 'img_source'])
            ->leftJoin('products', 'history.product_id', '=', 'products.id')
            ->where('user_id', $request->get('user_id'));
        $total = $items->count();
        $itemsCollection = ProductSearchResource::collection($items->offset(($page - 1) * $limit)->limit($limit)->get());

        return [
            "total" => $total,
            "isLastPage" => (count($itemsCollection) < $limit || $page * $limit >= $total),
            "items" => $itemsCollection
        ];
    }
    public function popular(Request $request): array
    {
        $page = intval($request->query("page")) > 0 ? intval($request->query("page")) : 1;
        $limit = 10;

        $items = DB::table('history')
            ->leftJoin('products', 'history.product_id', '=', 'products.id')
            ->where('history.created_at', '>=', date("Y-m-d", strtotime("-7 days")))
            ->groupBy('history.product_id');
        $total = $items->count();

        $items->selectRaw('barcode, name, img_local, img_source, COUNT(history.product_id) as counts')->orderByDesc('counts');
        $itemsCollection = ProductSearchResource::collection($items->limit($limit)->get());

        return [
            "total" => $total,
            "isLastPage" => (count($itemsCollection) < $limit || $page * $limit >= $total),
            "items" => $itemsCollection
        ];
    }
}
