<?php

namespace App\Services;

use illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use throwable;

class ProductService
{
    public function getProduct(int $productId): ?array
    {
        try {
            $response = Http::baseUrl(config('services.product.base_url'))
                ->get("/api/products/{$productId}");

            if ($response->successful() && isset($response['data'])) {
                return $response['data'];
            }
            return null;
        } catch (Throwable $e) {
            Log::error('Gagal mengambil data produk', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}