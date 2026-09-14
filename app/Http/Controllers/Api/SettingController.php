<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $shop = $this->shop($request);
        $setting = $shop->setting ?? $shop->setting()->create([]);

        return response()->json($setting);
    }

    public function update(Request $request): JsonResponse
    {
        $shop = $this->shop($request);

        $data = $request->validate([
            'email_from_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email'],
            'default_max_downloads' => ['nullable', 'integer', 'min:1'],
            'default_expiration_days' => ['nullable', 'integer', 'min:1'],
            'logo_url' => ['nullable', 'url'],
        ]);

        $setting = $shop->setting()->updateOrCreate([], $data);

        return response()->json($setting);
    }

    private function shop(Request $request): Shop
    {
        return $request->attributes->get('shop');
    }
}
