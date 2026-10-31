<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderConflictException;
use App\Exceptions\UpstreamServiceException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use App\Models\Order;

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

    public function show(int $id): JsonResponse
    {
        try {
            $order = Order::with('items')->find($id);

            if (!$order) {
                return response()->json([
                    'message' => "Pesanan dengan id {$id} tidak ditemukan.",
                ], 404);
            }

            return response()->json([
                'message' => 'Berhasil mengambil pesanan',
                'data' => $order,
            ], 200);

        } catch (Throwable $e) {
            report($e);

            $response = [
                'message' => 'Terjadi kesalahan saat mengambil pesanan.',
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }

        public function indexByUser(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:PENDING,PAID',
        ]);

        $orders = Order::with('items')
            ->where('user_id', $userId)
            ->when(
                isset($validated['status']),
                fn ($query) => $query->where('status', $validated['status'])
            )
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar pesanan',
            'data' => $orders,
        ], 200);
    }

    public function pay(int $id): JsonResponse
    {
        try {
            $updated = Order::whereKey($id)
                ->where('status', 'PENDING')
                ->update(['status' => 'PAID']);

            if (!$updated) {
                $order = Order::find($id);

                if (!$order) {
                    return response()->json([
                        'message' => "Pesanan dengan id {$id} tidak ditemukan.",
                    ], 404);
                }

                return response()->json([
                    'message' => 'Pesanan sudah dibayar atau tidak dapat dibayar kembali.',
                ], 409);
            }

            $order = Order::with('items')->find($id);

            return response()->json([
                'message' => 'Pembayaran berhasil',
                'data' => $order,
            ], 200);

        } catch (Throwable $e) {
            report($e);

            $response = [
                'message' => 'Terjadi kesalahan saat memproses pembayaran.',
            ];

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
            }

            return response()->json($response, 500);
        }
    }
}
