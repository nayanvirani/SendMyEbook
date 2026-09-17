@extends('admin.layout')

@section('title', 'Email Settings')

@section('content')
    <div class="max-w-2xl space-y-6">
        <p class="text-sm text-gray-600">
            This is the sender used as <strong>every shop's "SendMyEbook's own"</strong> email option
            (what a shop gets until it sets up its own SMTP/API provider in its Settings). Railway doesn't
            provide an email-sending service on its own — without a real provider configured here, this
            falls back to whatever <code class="rounded bg-gray-100 px-1 py-0.5">MAIL_MAILER</code> is set
            to on Railway, which starts out as <code class="rounded bg-gray-100 px-1 py-0.5">log</code>
            (nothing actually sends).
        </p>

        <form
            method="POST"
            action="{{ route('admin.settings.update') }}"
            class="space-y-5 rounded-xl border border-gray-200 bg-white p-6"
        >
            @csrf
            @method('PUT')

            <div>
                <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From email address</label>
                <input
                    id="mail_from_address" name="mail_from_address" type="email"
                    value="{{ old('mail_from_address', $setting->mail_from_address) }}"
                    placeholder="downloads@sendmyebook.com"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                @error('mail_from_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="mail_provider" class="block text-sm font-medium text-gray-700">Provider</label>
                <select
                    id="mail_provider" name="mail_provider" onchange="toggleProviderFields(this.value)"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                    @php $current = old('mail_provider', $setting->mail_provider); @endphp
                    <option value="" {{ $current === null || $current === '' ? 'selected' : '' }}>Use Railway's MAIL_MAILER env config</option>
                    <option value="smtp" {{ $current === 'smtp' ? 'selected' : '' }}>Custom SMTP</option>
                    <option value="mailgun" {{ $current === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                    <option value="sendgrid" {{ $current === 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                    <option value="postmark" {{ $current === 'postmark' ? 'selected' : '' }}>Postmark</option>
                    <option value="ses" {{ $current === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                    <option value="resend" {{ $current === 'resend' ? 'selected' : '' }}>Resend</option>
                </select>
                @error('mail_provider') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div id="fields-smtp" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_host" class="block text-sm font-medium text-gray-700">SMTP host</label>
                        <input id="smtp_host" name="smtp_host" type="text" value="{{ old('smtp_host', $setting->smtp_host) }}" placeholder="smtp.example.com"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div>
                        <label for="smtp_port" class="block text-sm font-medium text-gray-700">Port</label>
                        <input id="smtp_port" name="smtp_port" type="number" value="{{ old('smtp_port', $setting->smtp_port) }}" placeholder="587"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input id="smtp_username" name="smtp_username" type="text" value="{{ old('smtp_username', $setting->smtp_username) }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div>
                        <label for="smtp_password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="smtp_password" name="smtp_password" type="password"
                            placeholder="{{ $setting->smtp_password ? 'Saved — leave blank to keep it' : '' }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                </div>
                <div>
                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                    <select id="smtp_encryption" name="smtp_encryption"
                        class="mt-1 w-48 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        @php $enc = old('smtp_encryption', $setting->smtp_encryption); @endphp
                        <option value="" {{ ! $enc ? 'selected' : '' }}>None</option>
                        <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>
            </div>

            <div id="fields-mailgun" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <div>
                    <label for="mailgun_api_key" class="block text-sm font-medium text-gray-700">Mailgun API key</label>
                    <input id="mailgun_api_key" name="mailgun_api_key" type="password"
                        placeholder="{{ $setting->mailgun_api_key ? 'Saved — leave blank to keep it' : 'key-...' }}"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="mailgun_domain" class="block text-sm font-medium text-gray-700">Domain</label>
                        <input id="mailgun_domain" name="mailgun_domain" type="text" value="{{ old('mailgun_domain', $setting->mailgun_domain) }}" placeholder="mg.sendmyebook.com"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div>
                        <label for="mailgun_region" class="block text-sm font-medium text-gray-700">Region</label>
                        <select id="mailgun_region" name="mailgun_region"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                            @php $mgRegion = old('mailgun_region', $setting->mailgun_region); @endphp
                            <option value="us" {{ $mgRegion !== 'eu' ? 'selected' : '' }}>US</option>
                            <option value="eu" {{ $mgRegion === 'eu' ? 'selected' : '' }}>EU</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="fields-sendgrid" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <label for="sendgrid_api_key" class="block text-sm font-medium text-gray-700">SendGrid API key</label>
                <input id="sendgrid_api_key" name="sendgrid_api_key" type="password"
                    placeholder="{{ $setting->sendgrid_api_key ? 'Saved — leave blank to keep it' : 'SG....' }}"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div id="fields-postmark" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <label for="postmark_api_key" class="block text-sm font-medium text-gray-700">Postmark server token</label>
                <input id="postmark_api_key" name="postmark_api_key" type="password"
                    placeholder="{{ $setting->postmark_api_key ? 'Saved — leave blank to keep it' : '' }}"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div id="fields-ses" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ses_access_key_id" class="block text-sm font-medium text-gray-700">Access key ID</label>
                        <input id="ses_access_key_id" name="ses_access_key_id" type="text"
                            placeholder="{{ $setting->ses_access_key_id ? 'Saved — leave blank to keep it' : 'AKIA...' }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div>
                        <label for="ses_secret_access_key" class="block text-sm font-medium text-gray-700">Secret access key</label>
                        <input id="ses_secret_access_key" name="ses_secret_access_key" type="password"
                            placeholder="{{ $setting->ses_secret_access_key ? 'Saved — leave blank to keep it' : '' }}"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                </div>
                <div>
                    <label for="ses_region" class="block text-sm font-medium text-gray-700">Region</label>
                    <input id="ses_region" name="ses_region" type="text" value="{{ old('ses_region', $setting->ses_region) }}" placeholder="us-east-1"
                        class="mt-1 w-48 rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
            </div>

            <div id="fields-resend" class="hidden space-y-4 rounded-lg bg-gray-50 p-4">
                <label for="resend_api_key" class="block text-sm font-medium text-gray-700">Resend API key</label>
                <input id="resend_api_key" name="resend_api_key" type="password"
                    placeholder="{{ $setting->resend_api_key ? 'Saved — leave blank to keep it' : 're_...' }}"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">
                    Save changes
                </button>
            </div>
        </form>

        <form
            method="POST"
            action="{{ route('admin.settings.test-email') }}"
            class="flex items-end gap-3 rounded-xl border border-gray-200 bg-white p-6"
        >
            @csrf
            <div class="flex-1">
                <label for="test_email" class="block text-sm font-medium text-gray-700">Send a test email to</label>
                <input
                    id="test_email" name="test_email" type="email" required placeholder="you@example.com"
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
            </div>
            <button type="submit" class="rounded-md border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Send test
            </button>
        </form>
    </div>

    <script>
        function toggleProviderFields(provider) {
            document.querySelectorAll('[id^="fields-"]').forEach((el) => el.classList.add('hidden'));
            const target = document.getElementById('fields-' + provider);
            if (target) target.classList.remove('hidden');
        }
        toggleProviderFields(document.getElementById('mail_provider').value);
    </script>
@endsection
