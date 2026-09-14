<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Shop $shop */
        $shop = $request->attributes->get('shop');

        $totalDigitalProducts = $shop->digitalProducts()->count();
        $totalFiles = $shop->digitalProducts()->withCount('files')->get()->sum('files_count');
        $totalDownloads = Download::query()
            ->whereHas('downloadToken.digitalProduct', fn ($q) => $q->where('shop_id', $shop->id))
            ->count();

        $recentOrders = $shop->orders()->latest()->limit(5)->get([
            'id', 'shopify_order_number', 'customer_email', 'financial_status', 'total_price', 'created_at',
        ]);

        $topProducts = $shop->digitalProducts()
            ->withCount(['downloadTokens as downloads_count' => function ($query) {
                $query->join('downloads', 'downloads.download_token_id', '=', 'download_tokens.id');
            }])
            ->orderByDesc('downloads_count')
            ->limit(5)
            ->get(['id', 'shopify_product_title']);

        return response()->json([
            'total_digital_products' => $totalDigitalProducts,
            'total_files' => (int) $totalFiles,
            'total_downloads' => $totalDownloads,
            'storage_usage_bytes' => $this->storageUsageBytes($shop),
            'recent_orders' => $recentOrders,
            'top_downloaded_products' => $topProducts,
        ]);
    }

    private function storageUsageBytes(Shop $shop): int
    {
        return (int) $shop->digitalProducts()
            ->join('files', 'files.digital_product_id', '=', 'digital_products.id')
            ->sum('files.size_bytes');
    }
}
