<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShopController extends Controller
{
    public function index(): View
    {
        return view('admin.shops.index', [
            'shops' => Shop::query()
                ->withCount(['digitalProducts', 'orders'])
                ->with('activeSubscription.plan')
                ->latest('installed_at')
                ->paginate(25),
        ]);
    }

    public function toggleActive(Shop $shop): RedirectResponse
    {
        $shop->update(['is_active' => ! $shop->is_active]);

        return redirect()->route('admin.shops.index')->with('status', "{$shop->shop_domain} is now ".($shop->is_active ? 'active' : 'inactive').'.');
    }
}
