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
        $products = $this->catalogQuery()->get();

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

        $products = $this->catalogQuery()
            ->where(fn ($q) => $this->applySearch($q, $normalized, $slugQuery))
            ->limit($limit)
            ->get();

        return ProductAPIResource::collection($products)->response();
    }

    public function equipment()
    {
        $products = $this->catalogQuery()
        ->whereHas('categories', function ($categoryQuery) {
            $categoryQuery->where('slug', 'equipment');
        })
        ->get();

        return ProductAPIResource::collection($products);
    }

    public function equipmentSearch(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $limit = max(1, min((int) $request->query('limit', 10), 50));

        if ($query === '') {
            return response()->json(['data' => []]);
        }

        $normalized = mb_strtolower($query);
        $slugQuery = Str::slug($query);

        $products = $this->catalogQuery()
            ->whereHas('categories', function ($categoryQuery) {
                $categoryQuery->where('slug', 'equipment');
            })
            ->where(fn ($q) => $this->applySearch($q, $normalized, $slugQuery))
            ->limit($limit)
            ->get();

        return ProductAPIResource::collection($products)->response();
    }

    private function catalogQuery()
    {
        return Product::query()->with([
            'categories:id,name,slug',
            'images:id,product_id,image_path,is_primary,sort_order',
            'variants:id,product_id,size,price,sale_price,stock,quantity',
            'relatedProducts:id,name,slug,price,sale_price,stock,quantity',
            'relatedProducts.categories:id,name,slug',
            'relatedProducts.primaryImage:id,product_id,image_path,is_primary,sort_order',
            'relatedProducts.images:id,product_id,image_path,is_primary,sort_order',
        ]);
    }

    private function applySearch($query, string $normalized, string $slugQuery): void
    {
        $query->whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(extra_information, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereHas('variants', function ($variantQuery) use ($normalized) {
                $variantQuery
                    ->whereRaw('LOWER(COALESCE(size, \'\')) LIKE ?', ["%{$normalized}%"]);
            })
            ->orWhereHas('categories', function ($categoryQuery) use ($normalized, $slugQuery) {
                $categoryQuery
                    ->whereRaw('LOWER(COALESCE(name, \'\')) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$normalized}%"]);

                if ($slugQuery !== '') {
                    $categoryQuery->orWhereRaw('LOWER(slug) LIKE ?', ["%{$slugQuery}%"]);
                }
            });

        if ($slugQuery !== '') {
            $query->orWhereRaw('LOWER(slug) LIKE ?', ["%{$slugQuery}%"]);
        }
    }
}
