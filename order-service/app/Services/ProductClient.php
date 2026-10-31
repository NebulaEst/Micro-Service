<?php

namespace App\Services;

use App\Exceptions\UpstreamServiceException;
use App\Exceptions\OrderConflictException;
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
                ->get("/products/{$productId}");

            if ($response->status() === 404) {
                throw new OrderConflictException(
                    "Produk dengan ID {$productId} tidak ditemukan."
                );
            }

            if (! $response->successful()) {
                throw new UpstreamServiceException(
                    'Product Service tidak dapat dihubungi, coba lagi nanti.'
                );
            }

            $product = $response->json('data');

            if (
                ! is_array($product)
                || ! isset(
                    $product['name'],
                    $product['price'],
                    $product['stock']
                )
                || ! is_numeric($product['price'])
                || ! is_numeric($product['stock'])
            ) {
                throw new UpstreamServiceException(
                    'Format respons Product Service tidak valid.'
                );
            }

            return $product;

        } catch (OrderConflictException | UpstreamServiceException $e) {
            throw $e;

        } catch (Throwable $e) {
            report($e);

            throw new UpstreamServiceException(
                'Product Service tidak dapat dihubungi, coba lagi nanti.'
            );
        }
    }
}
