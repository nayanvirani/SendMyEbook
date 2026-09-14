<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => Plan::query()->orderBy('sort_order')->get(),
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

    public function destroy(Plan $plan): RedirectResponse
    {
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
            'max_downloads_per_month' => ['nullable', 'integer', 'min:1'],
            'features' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Checkboxes are absent from the request entirely when unchecked, so
        // read it explicitly rather than relying on the validated array.
        $validated['is_active'] = $request->boolean('is_active');

        $validated['features'] = array_values(array_filter(
            array_map('trim', explode(',', (string) $request->input('features', '')))
        ));

        return $validated;
    }
}
