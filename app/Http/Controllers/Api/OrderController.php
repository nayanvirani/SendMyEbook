<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $this->shop($request)->orders()
            ->with('downloadTokens.digitalProduct')
            ->latest()
            ->paginate(20);

        return response()->json(OrderResource::collection($orders)->response()->getData(true));
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->shop_id === $this->shop($request)->id, 403);

        return response()->json(new OrderResource($order->load('downloadTokens.digitalProduct')));
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
