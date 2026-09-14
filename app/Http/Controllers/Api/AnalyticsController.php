<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $shop = $this->shop($request);

        $baseQuery = fn () => Download::query()
            ->whereHas('downloadToken.digitalProduct', fn ($q) => $q->where('shop_id', $shop->id));

        $downloadsByDay = $baseQuery()
            ->select(DB::raw('DATE(downloaded_at) as date'), DB::raw('COUNT(*) as total'))
            ->where('downloaded_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = $shop->digitalProducts()
            ->withCount(['downloadTokens as downloads_count' => function ($query) {
                $query->join('downloads', 'downloads.download_token_id', '=', 'download_tokens.id');
            }])
            ->orderByDesc('downloads_count')
            ->limit(10)
            ->get(['id', 'shopify_product_title']);

        $recentActivity = $baseQuery()
            ->with(['file', 'downloadToken.order'])
            ->latest('downloaded_at')
            ->limit(20)
            ->get();

        return response()->json([
            'total_downloads' => $baseQuery()->count(),
            'downloads_by_day' => $downloadsByDay,
            'top_downloaded_products' => $topProducts,
            'recent_activity' => $recentActivity->map(fn (Download $d) => [
                'file_name' => $d->file->original_filename,
                'order_number' => $d->downloadToken->order->shopify_order_number,
                'downloaded_at' => $d->downloaded_at,
            ]),
        ]);
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
