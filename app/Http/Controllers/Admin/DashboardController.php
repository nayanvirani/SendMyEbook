<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeSubscriptions = Subscription::query()->where('status', 'active')->with('plan')->get();

        return view('admin.dashboard', [
            'totalShops' => Shop::query()->count(),
            'activeShops' => Shop::query()->where('is_active', true)->count(),
            'totalPlans' => Plan::query()->count(),
            'activeSubscriptions' => $activeSubscriptions->count(),
            'estimatedMrr' => $activeSubscriptions->sum(fn (Subscription $s) => (float) $s->plan->price),
            'recentShops' => Shop::query()->latest('installed_at')->limit(8)->get(),
        ]);
    }
}
