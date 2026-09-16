import '@shopify/ui-extensions/preact';
import { render } from 'preact';
import { useEffect, useState } from 'preact/hooks';

// This app's own Laravel backend — not part of the Shopify GraphQL APIs.
const APP_URL = 'https://sendmyebook-production.up.railway.app';

export default async () => {
    render(<Extension />, document.body);
};

// The order webhook → queue job → DownloadToken creation pipeline is
// asynchronous and this page can render moments after checkout completes,
// so the first fetch can race ahead of that pipeline and see no download
// tokens yet even though the order is entirely legitimate. Retry a few
// times with a short wait rather than treating an empty result as final.
const RETRY_DELAYS_MS = [1500, 1500, 2000, 2000, 3000];

function Extension() {
    const [downloads, setDownloads] = useState(null);

    useEffect(() => {
        loadDownloads().catch(() => setDownloads([]));
    }, []);

    async function loadDownloads() {
        // The exact API name for "the order this page is showing" isn't
        // fully consistent across extension targets/versions, so try the
        // ones actually documented for order-status/thank-you contexts
        // rather than assuming just one. shopify.order.value.id is
        // top-level; orderConfirmation.value.order.id is nested.
        const orderId = shopify.order?.value?.id ?? shopify.orderConfirmation?.value?.order?.id;

        if (!orderId) {
            setDownloads([]);
            return;
        }

        const numericOrderId = String(orderId).split('/').pop();

        for (let attempt = 0; attempt <= RETRY_DELAYS_MS.length; attempt++) {
            const token = await shopify.sessionToken.get();

            const response = await fetch(`${APP_URL}/api/storefront/orders/${numericOrderId}/downloads`, {
                headers: { Authorization: `Bearer ${token}` },
            });

            if (response.ok) {
                const body = await response.json();

                if (body.downloads?.length > 0 || attempt === RETRY_DELAYS_MS.length) {
                    setDownloads(body.downloads || []);
                    return;
                }
            } else if (attempt === RETRY_DELAYS_MS.length) {
                setDownloads([]);
                return;
            }

            await new Promise((resolve) => setTimeout(resolve, RETRY_DELAYS_MS[attempt]));
        }
    }

    if (downloads === null || downloads.length === 0) {
        return null;
    }

    return (
        <s-banner heading="Digital downloads">
            <s-stack direction="block" gap="base">
                {downloads.map((item) => (
                    <s-stack key={item.product_title} direction="block" gap="tight">
                        <s-text emphasis="bold">{item.product_title}</s-text>
                        {item.url ? (
                            <s-link href={item.url}>Download now</s-link>
                        ) : (
                            <s-text tone="subdued">This link isn't available right now — check your email.</s-text>
                        )}
                        {item.license_key && <s-text>License key: {item.license_key}</s-text>}
                    </s-stack>
                ))}
            </s-stack>
        </s-banner>
    );
}
