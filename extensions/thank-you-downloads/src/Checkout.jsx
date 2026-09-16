import '@shopify/ui-extensions/preact';
import { render } from 'preact';
import { useEffect, useState } from 'preact/hooks';

// This app's own Laravel backend — not part of the Shopify GraphQL APIs.
const APP_URL = 'https://sendmyebook-production.up.railway.app';

export default async () => {
    render(<Extension />, document.body);
};

function Extension() {
    const [downloads, setDownloads] = useState(null);

    useEffect(() => {
        loadDownloads().catch(() => setDownloads([]));
    }, []);

    async function loadDownloads() {
        // orderConfirmation.value is { order: { id }, number, isFirstOrder }
        // — the order id is nested under `order`, not on the value itself.
        const orderId = shopify.orderConfirmation?.value?.order?.id;

        if (!orderId) {
            setDownloads([]);
            return;
        }

        const numericOrderId = String(orderId).split('/').pop();
        const token = await shopify.sessionToken.get();

        const response = await fetch(`${APP_URL}/api/storefront/orders/${numericOrderId}/downloads`, {
            headers: { Authorization: `Bearer ${token}` },
        });

        if (!response.ok) {
            setDownloads([]);
            return;
        }

        const body = await response.json();
        setDownloads(body.downloads || []);
    }

    // Nothing to show yet, or this order has no digital products at all —
    // most orders won't, so stay silent rather than showing an empty block.
    if (downloads === null || downloads.length === 0) {
        return null;
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
