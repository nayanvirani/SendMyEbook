<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row table: the public Privacy Policy body, edited by the
 * platform owner from the super-admin panel instead of living hardcoded
 * in resources/views/legal/privacy.blade.php.
 */
class LegalContent extends Model
{
    protected $fillable = [
        'privacy_policy',
    ];

    /**
     * The current row, falling back to a fresh (unsaved) instance seeded
     * with the default copy so both the admin editor and the public page
     * always have something sensible to show before the first save.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static(['privacy_policy' => static::defaultPrivacyPolicy()]);
    }

    public static function defaultPrivacyPolicy(): string
    {
        return <<<'HTML'
            <p>
                SendMyEbook ("the App") is a Shopify application that lets merchants sell digital
                products (ebooks, PDFs, audio, video, and similar files) and automatically delivers
                them to customers after a successful purchase. This policy explains what data the
                App collects, why, and how it is protected.
            </p>

            <h2>Information we collect</h2>
            <p>The App only collects what it needs to deliver digital files and enforce a merchant's download rules:</p>
            <ul>
                <li><strong>Store information</strong> — your shop domain and an access token issued by Shopify when you install the App, used to call the Shopify Admin API on your behalf.</li>
                <li><strong>Product and order data</strong> — the Shopify products/variants a merchant maps to digital files, and order details (order number, financial status, line items) needed to detect a successful purchase.</li>
                <li><strong>Customer email address</strong> — taken from the order, used solely to send the digital-download email for that order.</li>
                <li><strong>Digital files</strong> — the files a merchant uploads, stored so they can be delivered to that merchant's customers.</li>
                <li><strong>Download activity</strong> — timestamps, IP address, and user agent recorded against each download link, used to enforce download limits and for the merchant's own analytics.</li>
            </ul>
            <p>We do not collect payment card details — all payments are processed by Shopify.</p>

            <h2>How we use this information</h2>
            <ul>
                <li>To detect a successful order and generate secure, time-limited download access for the customer who paid for it.</li>
                <li>To enforce the download limits, link expiration, and refund rules a merchant configures.</li>
                <li>To send the automatic delivery email, and to show merchants their own orders, downloads, and analytics inside the App.</li>
                <li>To operate billing for the App's own subscription plans via Shopify's billing API.</li>
            </ul>

            <h2>How this information is stored and protected</h2>
            <ul>
                <li>Digital files are stored in a private, access-controlled object storage bucket. Files are never exposed at a public URL — every download uses a signed, time-limited link generated at request time.</li>
                <li>Shopify access tokens are encrypted at rest.</li>
                <li>All data is stored in a private database and is not accessible to other merchants using the App — every query is scoped to the merchant's own store.</li>
                <li>All traffic to and from the App is encrypted in transit (HTTPS).</li>
            </ul>

            <h2>Sharing of information</h2>
            <p>
                We do not sell merchant or customer data, and we do not share it with third parties
                except the infrastructure providers required to run the App (hosting, database, and
                object storage) and Shopify itself. These providers only process data on our behalf
                and are not permitted to use it for their own purposes.
            </p>

            <h2>Data retention and deletion</h2>
            <ul>
                <li>If a merchant uninstalls the App, their store is marked inactive and no further access tokens are used.</li>
                <li>We honor Shopify's mandatory compliance webhooks: a customer's data-erasure request redacts their email/name from order records, and a shop-redaction request deletes all of that shop's data, including uploaded files.</li>
                <li>Merchants can delete a digital product or file at any time from within the App, which also removes it from storage.</li>
            </ul>

            <h2>Your rights</h2>
            <p>
                Merchants and their customers can request access to, correction of, or deletion of
                their data at any time by contacting us using the details below.
            </p>

            <h2>Contact</h2>
            <p>
                Questions about this policy or your data can be sent to
                <a href="mailto:viraninayan518@gmail.com" class="text-emerald-700 underline">viraninayan518@gmail.com</a>.
            </p>
            HTML;
    }
}
