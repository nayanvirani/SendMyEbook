import React, { useEffect, useState } from 'react';
import { Page, Layout, Card, Text, BlockStack, InlineGrid, SkeletonBodyText, Banner } from '@shopify/polaris';
import { useNavigate } from 'react-router-dom';
import { api } from '../api';

function StatCard({ label, value }) {
    return (
        <Card>
            <BlockStack gap="200">
                <Text as="span" variant="bodySm" tone="subdued">{label}</Text>
                <Text as="span" variant="headingLg">{value}</Text>
            </BlockStack>
        </Card>
    );
}

export default function Dashboard() {
    const navigate = useNavigate();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        api.get('/dashboard')
            .then(setData)
            .catch(() => setError('Could not load the dashboard. Try refreshing the page.'))
            .finally(() => setLoading(false));
    }, []);

    return (
        <Page
            title="Dashboard"
            primaryAction={{ content: 'Add Digital Product', onAction: () => navigate('/digital-products/new') }}
        >
            <Layout>
                <Layout.Section>
                    {error && <Banner tone="critical">{error}</Banner>}
                    {loading ? (
                        <Card><SkeletonBodyText lines={4} /></Card>
                    ) : !data ? null : (
                        <BlockStack gap="400">
                            <InlineGrid columns={{ xs: 1, sm: 2, md: 4 }} gap="400">
                                <StatCard label="Digital Products" value={data.total_digital_products} />
                                <StatCard label="Files" value={data.total_files} />
                                <StatCard label="Downloads" value={data.total_downloads} />
                                <StatCard label="Storage Used" value={formatBytes(data.storage_usage_bytes)} />
                            </InlineGrid>

                            <Card>
                                <BlockStack gap="300">
                                    <Text as="h2" variant="headingMd">Recent orders</Text>
                                    {data.recent_orders.length === 0 && (
                                        <Text as="p" tone="subdued">No orders yet.</Text>
                                    )}
                                    {data.recent_orders.map((order) => (
                                        <Text as="p" key={order.id}>
                                            #{order.shopify_order_number} — {order.customer_email ?? 'no email'} — {order.financial_status}
                                        </Text>
                                    ))}
                                </BlockStack>
                            </Card>

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
                        </BlockStack>
                    )}
                </Layout.Section>
            </Layout>
        </Page>
    );
}

function formatBytes(bytes) {
    if (!bytes) return '0 MB';
    const mb = bytes / (1024 * 1024);
    return mb >= 1024 ? `${(mb / 1024).toFixed(1)} GB` : `${mb.toFixed(1)} MB`;
}
