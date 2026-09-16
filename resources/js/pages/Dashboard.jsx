import React, { useEffect, useState } from 'react';
import { Page, Layout, Card, Text, BlockStack, InlineStack, InlineGrid, SkeletonBodyText, Banner, Badge, Icon, Box, Divider } from '@shopify/polaris';
import {
    ProductIcon,
    FileIcon,
    ArrowDownIcon,
    DatabaseIcon,
} from '@shopify/polaris-icons';
import { useNavigate } from 'react-router-dom';
import { api } from '../api';
import { formatBytes } from '../formatBytes';

const FINANCIAL_STATUS_TONE = {
    paid: 'success',
    refunded: 'critical',
    partially_refunded: 'warning',
    pending: 'attention',
};

function StatCard({ label, value, icon, tint }) {
    return (
        <Card>
            <InlineStack gap="300" blockAlign="center">
                <div style={{
                    width: '40px',
                    height: '40px',
                    borderRadius: '10px',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    flex: 'none',
                    background: tint,
                }}>
                    <Icon source={icon} tone="base" />
                </div>
                <BlockStack gap="050">
                    <Text as="span" variant="bodySm" tone="subdued">{label}</Text>
                    <Text as="span" variant="headingLg">{value}</Text>
                </BlockStack>
            </InlineStack>
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

    const maxDownloads = data ? Math.max(1, ...data.top_downloaded_products.map((p) => p.downloads_count)) : 1;

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
                                <StatCard label="Digital Products" value={data.total_digital_products} icon={ProductIcon} tint="#e8f5f0" />
                                <StatCard label="Files" value={data.total_files} icon={FileIcon} tint="#eaf1fd" />
                                <StatCard label="Downloads" value={data.total_downloads} icon={ArrowDownIcon} tint="#f3edfb" />
                                <StatCard label="Storage Used" value={formatBytes(data.storage_usage_bytes)} icon={DatabaseIcon} tint="#fdf3e7" />
                            </InlineGrid>

                            <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
                                <Card padding="0">
                                    <Box padding="400" paddingBlockEnd="300">
                                        <Text as="h2" variant="headingMd">Recent orders</Text>
                                    </Box>
                                    {data.recent_orders.length === 0 ? (
                                        <Box padding="400" paddingBlockStart="0">
                                            <Text as="p" tone="subdued">Orders will appear here once a customer checks out.</Text>
                                        </Box>
                                    ) : (
                                        <BlockStack gap="0">
                                            {data.recent_orders.map((order, i) => (
                                                <React.Fragment key={order.id}>
                                                    {i > 0 && <Divider />}
                                                    <Box padding="300" paddingInlineStart="400" paddingInlineEnd="400">
                                                        <InlineStack align="space-between" blockAlign="center">
                                                            <BlockStack gap="0">
                                                                <Text as="span" fontWeight="medium">#{order.shopify_order_number}</Text>
                                                                <Text as="span" variant="bodySm" tone="subdued">{order.customer_email ?? 'No email'}</Text>
                                                            </BlockStack>
                                                            <Badge tone={FINANCIAL_STATUS_TONE[order.financial_status] ?? 'subdued'}>
                                                                {order.financial_status ?? 'unknown'}
                                                            </Badge>
                                                        </InlineStack>
                                                    </Box>
                                                </React.Fragment>
                                            ))}
                                        </BlockStack>
                                    )}
                                </Card>

                                <Card padding="0">
                                    <Box padding="400" paddingBlockEnd="300">
                                        <Text as="h2" variant="headingMd">Top downloaded products</Text>
                                    </Box>
                                    {data.top_downloaded_products.length === 0 ? (
                                        <Box padding="400" paddingBlockStart="0">
                                            <Text as="p" tone="subdued">Your best-selling digital products will show up here.</Text>
                                        </Box>
                                    ) : (
                                        <BlockStack gap="0">
                                            {data.top_downloaded_products.map((product, i) => (
                                                <React.Fragment key={product.id}>
                                                    {i > 0 && <Divider />}
                                                    <Box padding="300" paddingInlineStart="400" paddingInlineEnd="400">
                                                        <BlockStack gap="150">
                                                            <InlineStack align="space-between" blockAlign="center">
                                                                <Text as="span" fontWeight="medium">{product.shopify_product_title}</Text>
                                                                <Text as="span" variant="bodySm" tone="subdued">{product.downloads_count}</Text>
                                                            </InlineStack>
                                                            <div style={{ height: '6px', borderRadius: '999px', background: '#f1f2f4', overflow: 'hidden' }}>
                                                                <div style={{
                                                                    height: '100%',
                                                                    width: `${Math.max(6, (product.downloads_count / maxDownloads) * 100)}%`,
                                                                    borderRadius: '999px',
                                                                    background: '#008060',
                                                                }} />
                                                            </div>
                                                        </BlockStack>
                                                    </Box>
                                                </React.Fragment>
                                            ))}
                                        </BlockStack>
                                    )}
                                </Card>
                            </InlineGrid>
                        </BlockStack>
                    )}
                </Layout.Section>
            </Layout>
        </Page>
    );
}
