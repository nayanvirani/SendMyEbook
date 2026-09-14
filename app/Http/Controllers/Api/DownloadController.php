<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DownloadResource;
use App\Models\Download;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shop = $this->shop($request);

        $downloads = Download::query()
            ->with(['file', 'downloadToken.order', 'downloadToken.digitalProduct'])
            ->whereHas('downloadToken.digitalProduct', fn ($q) => $q->where('shop_id', $shop->id))
            ->latest('downloaded_at')
            ->paginate(25);

        return response()->json(DownloadResource::collection($downloads)->response()->getData(true));
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
