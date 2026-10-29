<?php

namespace App\Services;

use App\Exceptions\UpstreamServiceException;
use Illuminate\Support\Facades\Http;
use Throwable;

class ProductClient
{
    public function getProduct(int $productId): array
    {
        try {
            $response = Http::baseUrl(
                config('services.product.base_url')
            )
                ->timeout(5)
                ->get("/api/products/{$productId}");

            if (! $response->successful()) {
                throw new UpstreamServiceException(
                    "Gagal mengambil produk dengan ID {$productId}."
                );
            }

            $product = $response->json('data');

            if (
                ! is_array($product)
                || ! isset($product['name'], $product['price'])
                || ! is_numeric($product['price'])
            ) {
                throw new UpstreamServiceException(
                    'Format respons Product Service tidak valid.'
                );
            }

            return $product;
        } catch (UpstreamServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new UpstreamServiceException(
                'Product Service tidak dapat diakses.'
            );
        }
    }
}