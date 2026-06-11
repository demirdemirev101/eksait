<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductAPIResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class ProductApiController extends Controller
{
    /**
     * Display a listing of the products, including their categories and images as a JSON resource collection.
     *
     * @return AnonymousResourceCollection
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
            'categories:id,name,name_en,name_de,slug',
            'images:id,product_id,image_path,is_primary,sort_order',
            'variants:id,product_id,size,size_en,size_de,price,sale_price,stock,quantity',
            'relatedProducts:id,name,name_en,name_de,slug,price,sale_price,stock,quantity,description,description_en,description_de,extra_information,extra_information_en,extra_information_de',
            'relatedProducts.categories:id,name,name_en,name_de,slug',
            'relatedProducts.primaryImage:id,product_id,image_path,is_primary,sort_order',
            'relatedProducts.images:id,product_id,image_path,is_primary,sort_order',
        ]);
    }

    private function applySearch($query, string $normalized, string $slugQuery): void
    {
        $query->whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(name_en, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(name_de, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(description_en, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(description_de, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(extra_information, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(extra_information_en, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereRaw('LOWER(COALESCE(extra_information_de, \'\')) LIKE ?', ["%{$normalized}%"])
            ->orWhereHas('variants', function ($variantQuery) use ($normalized) {
                $variantQuery->where(function ($sizeQuery) use ($normalized) {
                    $sizeQuery
                        ->whereRaw('LOWER(COALESCE(size, \'\')) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(COALESCE(size_en, \'\')) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(COALESCE(size_de, \'\')) LIKE ?', ["%{$normalized}%"]);
                });
            })
            ->orWhereHas('categories', function ($categoryQuery) use ($normalized, $slugQuery) {
                $categoryQuery
                    ->whereRaw('LOWER(COALESCE(name, \'\')) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(COALESCE(name_en, \'\')) LIKE ?', ["%{$normalized}%"])
                    ->orWhereRaw('LOWER(COALESCE(name_de, \'\')) LIKE ?', ["%{$normalized}%"])
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
