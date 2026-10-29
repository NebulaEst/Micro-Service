<?php

namespace App\Services;

use App\Exceptions\UpstreamServiceException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{
    public function __construct(
        private CartClient $cartClient,
        private ProductClient $productClient
    ) {}

    public function checkout(int $userId): array
    {
        $cart = $this->cartClient->getCart($userId);

        if (empty($cart['items'])) {
            throw new UpstreamServiceException(
                'Keranjang kosong.'
            );
        }

        $items = [];
        $totalAmount = 0;

        foreach ($cart['items'] as $cartItem) {
            $productId = (int) $cartItem['product_id'];
            $quantity = (int) $cartItem['quantity'];

            if ($quantity < 1) {
                throw new UpstreamServiceException(
                    'Jumlah produk dalam keranjang tidak valid.'
                );
            }

            $product = $this->productClient->getProduct($productId);

            $unitPrice = (float) $product['price'];
            $subtotal = round($unitPrice * $quantity, 2);

            $items[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            $totalAmount += $subtotal;
        }

        return DB::transaction(function () use (
            $userId,
            $items,
            $totalAmount
        ): array {
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'PENDING',
                'total_amount' => round($totalAmount, 2),
            ]);

            $order->items()->createMany($items);

            return $order->load('items')->toArray();
        });
    }
}