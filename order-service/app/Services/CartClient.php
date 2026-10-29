<?php

namespace App\Services;

use App\Exceptions\UpstreamServiceException;
use Illuminate\Support\Facades\Http;
use Throwable;

class CartClient
{
    public function getCart(int $userId): array
    {
        try {
            $response = Http::baseUrl(
                config('services.cart.base_url')
            )
                ->timeout(5)
                ->get("/api/carts/{$userId}");

            if (! $response->successful()) {
                throw new UpstreamServiceException(
                    'Gagal mengambil keranjang dari Cart Service.'
                );
            }

            $cart = $response->json('data');

            if (! is_array($cart) || ! isset($cart['items']) || ! is_array($cart['items'])) {
                throw new UpstreamServiceException(
                    'Format respons Cart Service tidak valid.'
                );
            }

            return $cart;
        } catch (UpstreamServiceException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new UpstreamServiceException(
                'Cart Service tidak dapat diakses.'
            );
        }
    }
}