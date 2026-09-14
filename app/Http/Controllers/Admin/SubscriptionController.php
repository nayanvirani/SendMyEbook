<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::query()
                ->with(['shop', 'plan'])
                ->latest()
                ->paginate(25),
        ]);
    }
}
