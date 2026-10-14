<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Shopify\PartnerApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Sourced entirely from the Partner API (see PartnerApiClient) — real
 * transaction records Shopify itself processed, not derived from the
 * dashboard's own Plan/Subscription snapshot (which only reflects the
 * current moment and says nothing about a shop that paid for months and
 * then left).
 */
class RevenueController extends Controller
{
    public function index(Request $request, PartnerApiClient $client): View
    {
        $selectedMonth = $request->string('month')->toString() ?: null;

        if (! $client->isConfigured()) {
            return view('admin.revenue.index', [
                'configured' => false,
                'months' => [],
                'transactions' => [],
                'selectedMonth' => $selectedMonth,
                'sharePct' => config('shopify.revenue_share_pct'),
            ]);
        }

        $transactions = $client->getAppTransactions();
        $months = $client->groupByMonth($transactions);

        return view('admin.revenue.index', [
            'configured' => true,
            'months' => $months,
            'transactions' => $transactions,
            'selectedMonth' => $selectedMonth,
            'sharePct' => config('shopify.revenue_share_pct'),
        ]);
    }
}
