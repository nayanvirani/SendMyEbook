<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Shop;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AppSubscriptionsUpdateWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function post(Shop $shop, array $payload): TestResponse
    {
        $body = json_encode($payload);
        $hmac = base64_encode(hash_hmac('sha256', $body, (string) config('shopify.api_secret'), true));

        return $this->call(
            'POST',
            '/api/webhooks/app-subscriptions-update',
            [],
            [],
            [],
            [
                'HTTP_X-Shopify-Hmac-Sha256' => $hmac,
                'HTTP_X-Shopify-Shop-Domain' => $shop->shop_domain,
                'CONTENT_TYPE' => 'application/json',
            ],
            $body,
        );
    }

    public function test_activating_a_new_subscription_cancels_the_shops_other_active_subscription(): void
    {
        config(['shopify.api_secret' => 'test-secret']);

        $shop = Shop::factory()->create();
        $growth = Plan::factory()->create(['handle' => 'growth']);
        $pro = Plan::factory()->create(['handle' => 'pro']);

        $existing = Subscription::factory()->create([
            'shop_id' => $shop->id,
            'plan_id' => $growth->id,
            'shopify_charge_id' => 'gid://shopify/AppSubscription/1',
            'status' => 'active',
        ]);

        $response = $this->post($shop, [
            'app_subscription' => [
                'admin_graphql_api_id' => 'gid://shopify/AppSubscription/2',
                'name' => 'pro',
                'status' => 'active',
            ],
        ]);

        $response->assertOk();

        $this->assertSame('cancelled', $existing->refresh()->status);
        $this->assertSame('active', Subscription::query()
            ->where('shop_id', $shop->id)
            ->where('shopify_charge_id', 'gid://shopify/AppSubscription/2')
            ->value('status'));
        $this->assertSame($pro->id, Subscription::query()
            ->where('shopify_charge_id', 'gid://shopify/AppSubscription/2')
            ->value('plan_id'));
        $this->assertSame($shop->id, $shop->activeSubscription()->first()->shop_id);
    }

    public function test_a_non_active_status_does_not_touch_other_subscriptions(): void
    {
        config(['shopify.api_secret' => 'test-secret']);

        $shop = Shop::factory()->create();
        $growth = Plan::factory()->create(['handle' => 'growth']);
        Plan::factory()->create(['handle' => 'pro']);

        $existing = Subscription::factory()->create([
            'shop_id' => $shop->id,
            'plan_id' => $growth->id,
            'shopify_charge_id' => 'gid://shopify/AppSubscription/1',
            'status' => 'active',
        ]);

        $response = $this->post($shop, [
            'app_subscription' => [
                'admin_graphql_api_id' => 'gid://shopify/AppSubscription/2',
                'name' => 'pro',
                'status' => 'declined',
            ],
        ]);

        $response->assertOk();

        $this->assertSame('active', $existing->refresh()->status);
    }
}
