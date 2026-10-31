<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderConflictException;
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
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order,
            ], 201);

        } catch (OrderConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);

        } catch (UpstreamServiceException $e) {
            report($e);

            return response()->json([
                'message' => 'Service terkait tidak dapat dihubungi, coba lagi nanti.',
            ], 503);

        } catch (Throwable $e) {
            report($e);

            $response = [
                'message' => 'Terjadi kesalahan saat checkout.',
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }
}
