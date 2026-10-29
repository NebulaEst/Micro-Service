<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Order request validated successfully.',
            'data' => [
                'user_id' => $request->validated('user_id'),
            ],
        ], 200);
    }
}
