import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, Text, InlineGrid, SkeletonBodyText } from '@shopify/polaris';
import { api } from '../api';

export default function Analytics() {
    const [data, setData] = useState(null);

    useEffect(() => {
        api.get('/analytics').then(setData).catch(() => {});
    }, []);

    if (!data) {
        return (
            <Page title="Analytics">
                <Card><SkeletonBodyText lines={6} /></Card>
            </Page>
        );
    }

    return (
        <Page title="Analytics">
            <BlockStack gap="400">
                <InlineGrid columns={{ xs: 1, sm: 2 }} gap="400">
                    <Card>
                        <BlockStack gap="200">
                            <Text as="span" variant="bodySm" tone="subdued">Total downloads</Text>
                            <Text as="span" variant="headingLg">{data.total_downloads}</Text>
                        </BlockStack>
                    </Card>
                </InlineGrid>

                <Card>
                    <BlockStack gap="300">
                        <Text as="h2" variant="headingMd">Top downloaded products</Text>
                        {data.top_downloaded_products.length === 0 && (
                            <Text as="p" tone="subdued">No downloads yet.</Text>
                        )}
                        {data.top_downloaded_products.map((product) => (
                            <Text as="p" key={product.id}>
                                {product.shopify_product_title} — {product.downloads_count} downloads
                            </Text>
                        ))}
                    </BlockStack>
                </Card>

                <Card>
                    <BlockStack gap="300">
                        <Text as="h2" variant="headingMd">Recent activity</Text>
                        {data.recent_activity.length === 0 && (
                            <Text as="p" tone="subdued">No activity yet.</Text>
                        )}
                        {data.recent_activity.map((item, i) => (
                            <Text as="p" key={i}>
                                {item.file_name} — order #{item.order_number} — {new Date(item.downloaded_at).toLocaleString()}
                            </Text>
                        ))}
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
}
