@extends('admin.layout')

@section('title', 'Legal')

@section('content')
    <x-admin.page-header title="Legal" description="Edit the public Privacy Policy shown at /privacy." />

    <div class="max-w-3xl space-y-6">
        <p class="text-sm text-gray-600">
            Raw HTML — use <code class="rounded bg-gray-100 px-1 py-0.5">&lt;h2&gt;</code>,
            <code class="rounded bg-gray-100 px-1 py-0.5">&lt;p&gt;</code> and
            <code class="rounded bg-gray-100 px-1 py-0.5">&lt;ul&gt;/&lt;li&gt;</code> for section headings,
            paragraphs and bullet lists, matching the existing content below.
        </p>

        <form
            method="POST"
            action="{{ route('admin.legal.update') }}"
            class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6"
        >
            @csrf
            @method('PUT')

            <div>
                <label for="privacy_policy" class="block text-sm font-medium text-gray-700">Privacy Policy content</label>
                <textarea
                    id="privacy_policy" name="privacy_policy" rows="26"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 font-mono text-xs leading-relaxed shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >{{ old('privacy_policy', $content->privacy_policy) }}</textarea>
                @error('privacy_policy') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                    Save changes
                </button>
                <a href="{{ route('legal.privacy') }}" target="_blank" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    View live page →
                </a>
            </div>
        </form>
    </div>
@endsection
