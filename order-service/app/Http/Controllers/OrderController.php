<?php

namespace App\Http\Controllers;

use App\Exceptions\UpstreamServiceException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->checkout(
                (int) $request->validated('user_id')
            );

            return response()->json([
                'message' => 'Checkout berhasil.',
                'data' => $order,
            ], 201);
        } catch (UpstreamServiceException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat checkout.',
            ], 500);
        }
    }
}