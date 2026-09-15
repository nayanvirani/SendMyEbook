import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, Text, InlineGrid, SkeletonBodyText, ButtonGroup, Button } from '@shopify/polaris';
import { api } from '../api';

export default function Analytics() {
    const [data, setData] = useState(null);
    const [period, setPeriod] = useState('day');

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

    const series = period === 'day' ? data.downloads_by_day : data.downloads_by_month;

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
                    <Card>
                        <BlockStack gap="200">
                            <Text as="span" variant="bodySm" tone="subdued">Storage used</Text>
                            <Text as="span" variant="headingLg">{formatBytes(data.storage_usage_bytes)}</Text>
                        </BlockStack>
                    </Card>
                </InlineGrid>

                <Card>
                    <BlockStack gap="300">
                        <InlineGrid columns={{ xs: 1, sm: '1fr auto' }} gap="200">
                            <Text as="h2" variant="headingMd">Downloads over time</Text>
                            <ButtonGroup variant="segmented">
                                <Button pressed={period === 'day'} onClick={() => setPeriod('day')}>By day</Button>
                                <Button pressed={period === 'month'} onClick={() => setPeriod('month')}>By month</Button>
                            </ButtonGroup>
                        </InlineGrid>
                        {series.length === 0 ? (
                            <Text as="p" tone="subdued">No downloads in this range yet.</Text>
                        ) : (
                            <BlockStack gap="150">
                                {series.map((row) => (
                                    <InlineGrid key={row.period} columns={2}>
                                        <Text as="span" tone="subdued">{row.period}</Text>
                                        <Text as="span">{row.total}</Text>
                                    </InlineGrid>
                                ))}
                            </BlockStack>
                        )}
                    </BlockStack>
                </Card>

                <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
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
                            <Text as="h2" variant="headingMd">Downloads per order</Text>
                            {data.downloads_per_order.length === 0 && (
                                <Text as="p" tone="subdued">No downloads yet.</Text>
                            )}
                            {data.downloads_per_order.map((order) => (
                                <Text as="p" key={order.id}>
                                    #{order.shopify_order_number} — {order.downloads_count} downloads
                                </Text>
                            ))}
                        </BlockStack>
                    </Card>
                </InlineGrid>

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

function formatBytes(bytes) {
    if (!bytes) return '0 MB';
    const mb = bytes / (1024 * 1024);
    return mb >= 1024 ? `${(mb / 1024).toFixed(1)} GB` : `${mb.toFixed(1)} MB`;
}
