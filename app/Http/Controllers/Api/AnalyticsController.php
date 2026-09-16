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

        $dailyCounts = $baseQuery()
            ->select(DB::raw('DATE(downloaded_at) as period'), DB::raw('COUNT(*) as total'))
            ->where('downloaded_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('period')
            ->pluck('total', 'period');

        // A query that only returns days/months with activity renders as
        // one lone bar filling the whole chart when there's just one —
        // real time-series charts show the full period with zero-height
        // gaps, so the frontend needs every day/month represented.
        $downloadsByDay = collect(range(0, 29))
            ->map(fn ($i) => now()->subDays(29 - $i)->format('Y-m-d'))
            ->map(fn ($date) => ['period' => $date, 'total' => (int) ($dailyCounts[$date] ?? 0)])
            ->values();

        $monthlyCounts = $baseQuery()
            ->select(DB::raw("to_char(downloaded_at, 'YYYY-MM') as period"), DB::raw('COUNT(*) as total'))
            ->where('downloaded_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('period')
            ->pluck('total', 'period');

        $downloadsByMonth = collect(range(0, 11))
            ->map(fn ($i) => now()->subMonths(11 - $i)->format('Y-m'))
            ->map(fn ($month) => ['period' => $month, 'total' => (int) ($monthlyCounts[$month] ?? 0)])
            ->values();

        $topProducts = $shop->digitalProducts()
            ->select('digital_products.id', 'digital_products.shopify_product_title')
            ->withCount(['downloadTokens as downloads_count' => function ($query) {
                $query->join('downloads', 'downloads.download_token_id', '=', 'download_tokens.id');
            }])
            ->orderByDesc('downloads_count')
            ->limit(10)
            ->get();

        // Postgres can't reference a withCount() subquery alias in HAVING
        // (unlike ORDER BY, which works fine on the same alias below), so
        // the zero-download filter happens in PHP after fetching instead.
        // withCount() also overrides an explicit get([...]) column list
        // (it already sets $query->columns, so the "once" column
        // restriction never applies) — select explicitly beforehand so
        // the response doesn't carry the full Order row (customer PII,
        // raw Shopify payload) for a widget that only needs two fields.
        $downloadsPerOrder = $shop->orders()
            ->select('orders.id', 'orders.shopify_order_number')
            ->withCount(['downloadTokens as downloads_count' => function ($query) {
                $query->join('downloads', 'downloads.download_token_id', '=', 'download_tokens.id');
            }])
            ->orderByDesc('downloads_count')
            ->limit(10)
            ->get()
            ->filter(fn ($order) => $order->downloads_count > 0)
            ->values();

        $recentActivity = $baseQuery()
            ->with(['file', 'downloadToken.order'])
            ->latest('downloaded_at')
            ->limit(20)
            ->get();

        return response()->json([
            'total_downloads' => $baseQuery()->count(),
            'downloads_by_day' => $downloadsByDay,
            'downloads_by_month' => $downloadsByMonth,
            'top_downloaded_products' => $topProducts,
            'downloads_per_order' => $downloadsPerOrder,
            'storage_usage_bytes' => $this->storageUsageBytes($shop),
            'recent_activity' => $recentActivity->map(fn (Download $d) => [
                'file_name' => $d->file->original_filename,
                'order_number' => $d->downloadToken->order->shopify_order_number,
                'downloaded_at' => $d->downloaded_at,
            ]),
        ]);
    }

    private function storageUsageBytes(Shop $shop): int
    {
        return (int) $shop->digitalProducts()
            ->join('files', 'files.digital_product_id', '=', 'digital_products.id')
            ->sum('files.size_bytes');
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
