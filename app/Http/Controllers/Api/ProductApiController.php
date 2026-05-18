<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductAPIResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    /**
     * Display a listing of the products, including their categories and images as a JSON resource collection.
      *
      * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $products = Product::with([
            'categories:id,name,slug',
            'images:id,product_id,image_path,is_primary,sort_order',
            'variants:id,product_id,size,price,sale_price,stock,quantity,weight,width,height,length',
            'relatedProducts:id,name,slug,price,sale_price,stock,quantity',
            'relatedProducts.primaryImage:id,product_id,image_path,is_primary,sort_order',
            'relatedProducts.images:id,product_id,image_path,is_primary,sort_order',
        ])->get();

        return ProductAPIResource::collection($products);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $limit = max(1, min((int) $request->query('limit', 10), 50));

        if ($query === '') {
            return response()->json(['data' => []]);
        }

        $normalized = mb_strtolower($query);
        $slugQuery = Str::slug($query);

        $products = Product::query()
            ->with([
                'categories:id,name,slug',
                'images:id,product_id,image_path,is_primary,sort_order',
                'variants:id,product_id,size,price,sale_price,stock,quantity,weight,width,height,length',
                'relatedProducts:id,name,slug,price,sale_price,stock,quantity',
                'relatedProducts.primaryImage:id,product_id,image_path,is_primary,sort_order',
                'relatedProducts.images:id,product_id,image_path,is_primary,sort_order',
            ])
            ->where(function ($q) use ($normalized, $slugQuery) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(extra_information) LIKE ?', ["%{$normalized}%"])
                    ->orWhereHas('variants', function ($variantQuery) use ($normalized) {
                        $variantQuery->whereRaw('LOWER(size) LIKE ?', ["%{$normalized}%"]);
                    })
                    ->orWhereHas('categories', function ($categoryQuery) use ($normalized, $slugQuery) {
                        $categoryQuery
                            ->whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
                            ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$normalized}%"]);

                        if ($slugQuery !== '') {
                            $categoryQuery->orWhereRaw('LOWER(slug) LIKE ?', ["%{$slugQuery}%"]);
                        }
                    });

                if ($slugQuery !== '') {
                    $q->orWhereRaw('LOWER(slug) LIKE ?', ["%{$slugQuery}%"]);
                }
            })
            ->limit($limit)
            ->get();

        return ProductAPIResource::collection($products)->response();
    }
}
