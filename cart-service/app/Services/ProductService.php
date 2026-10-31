<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProductService
{
    public function getProduct(int $productId): ?array
    {
        try {
            $response = Http::baseUrl(config('services.product.base_url'))
                ->get("/products/{$productId}");

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