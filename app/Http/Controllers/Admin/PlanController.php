<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request): View
    {
        $showRetired = $request->boolean('retired');

        return view('admin.plans.index', [
            'plans' => Plan::query()
                ->withCount('subscriptions')
                ->when(! $showRetired, fn ($q) => $q->where('is_active', true))
                ->orderBy('sort_order')
                ->get(),
            'showRetired' => $showRetired,
        ]);
    }

    public function create(): View
    {
        return view('admin.plans.form', ['plan' => new Plan]);
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('status', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return redirect()->route('admin.plans.index')->with('status', 'Plan updated.');
    }

    /**
     * A plan with real subscription history can't be hard-deleted — it's
     * referenced by subscriptions.plan_id, and that history needs to stay
     * intact for shops that were ever on it. Deactivating instead keeps it
     * out of the pricing list and new-subscription lookups, which is what
     * "remove this plan" actually means in practice once it's been used.
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            $plan->update(['is_active' => false]);

            return redirect()->route('admin.plans.index')
                ->with('status', "\"{$plan->name}\" has subscription history, so it can't be deleted — deactivated instead and hidden from the pricing list.");
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('status', 'Plan deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Plan $plan = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'required', 'string', 'max:255', 'alpha_dash',
                'unique:plans,handle'.($plan?->id ? ",{$plan->id}" : ''),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'max_digital_products' => ['nullable', 'integer', 'min:1'],
            'max_deliveries_per_month' => ['nullable', 'integer', 'min:1'],
            'max_file_size_mb' => ['nullable', 'integer', 'min:1'],
            'trial_days' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'in:'.implode(',', array_keys(Plan::KNOWN_FEATURES))],
            'marketing_bullets' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Checkboxes are absent from the request entirely when unchecked, so
        // read it explicitly rather than relying on the validated array.
        $validated['is_active'] = $request->boolean('is_active');
        $validated['features'] = $validated['features'] ?? [];

        // One bullet per line in the textarea — blank lines dropped.
        $validated['marketing_bullets'] = array_values(array_filter(
            array_map('trim', explode("\n", (string) $request->input('marketing_bullets', '')))
        ));

        return $validated;
    }
}
