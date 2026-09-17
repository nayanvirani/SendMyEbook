import '@shopify/ui-extensions/preact';
import { render } from 'preact';
import { useEffect, useState } from 'preact/hooks';

// This app's own Laravel backend — not part of the Shopify GraphQL APIs.
const APP_URL = 'https://sendmyebook-production.up.railway.app';

export default async () => {
    render(<Extension />, document.body);
};

// The order webhook → queue job → DownloadToken creation pipeline is
// asynchronous and this page renders almost immediately after checkout
// completes, so the first fetch can easily race ahead of that pipeline
// and see no download tokens yet even though the order is entirely
// legitimate. Retry a few times with a short wait rather than treating
// an empty result as final on the very first try.
const RETRY_DELAYS_MS = [1500, 1500, 2000, 2000, 3000];

function Extension() {
    // 'checking' while retries are still in flight, 'ready' once real
    // downloads are found, 'empty' once every retry is exhausted with
    // nothing — every order (including ones with no digital product at
    // all) passes through 'checking' first, since there's no way to
    // know in advance whether this one will end up having downloads.
    const [status, setStatus] = useState('checking');
    const [downloads, setDownloads] = useState([]);

    useEffect(() => {
        loadDownloads().catch(() => setStatus('empty'));
    }, []);

    async function loadDownloads() {
        // orderConfirmation.value is { order: { id }, number, isFirstOrder }
        // — the order id is nested under `order`, not on the value itself.
        const orderId = shopify.orderConfirmation?.value?.order?.id;

        if (!orderId) {
            setStatus('empty');
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

                if (body.downloads?.length > 0) {
                    setDownloads(body.downloads);
                    setStatus('ready');
                    return;
                }

                if (attempt === RETRY_DELAYS_MS.length) {
                    setStatus('empty');
                    return;
                }
            } else if (attempt === RETRY_DELAYS_MS.length) {
                setStatus('empty');
                return;
            }

            await new Promise((resolve) => setTimeout(resolve, RETRY_DELAYS_MS[attempt]));
        }
    }

    if (status === 'empty') {
        return null;
    }

    if (status === 'checking') {
        return (
            <s-banner>
                <s-stack direction="inline" gap="tight" alignItems="center">
                    <s-spinner size="small" accessibilityLabel="Preparing your download" />
                    <s-text>Your digital download is being prepared — hang tight…</s-text>
                </s-stack>
            </s-banner>
        );
    }

    return (
        <s-banner heading="Your digital downloads">
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
                <s-text tone="subdued">We've also emailed you these links.</s-text>
            </s-stack>
        </s-banner>
    );
}
