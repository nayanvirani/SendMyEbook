<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lets the platform owner edit the public Privacy Policy body (rendered
 * at /privacy) without a code deploy.
 */
class LegalContentController extends Controller
{
    public function edit(): View
    {
        return view('admin.legal.edit', [
            'content' => LegalContent::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'privacy_policy' => ['required', 'string'],
        ]);

        $content = LegalContent::query()->first();

        if ($content) {
            $content->update($data);
        } else {
            LegalContent::create($data);
        }

        return redirect()->route('admin.legal.edit')->with('status', 'Privacy policy saved.');
    }
}
