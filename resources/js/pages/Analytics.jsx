import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, Text, InlineGrid, InlineStack, SkeletonBodyText, Button, Icon, Box, Divider } from '@shopify/polaris';
import { ArrowDownIcon, DatabaseIcon, ChartVerticalIcon, ReceiptIcon } from '@shopify/polaris-icons';
import BarChart from '../components/BarChart';
import { api } from '../api';

function StatTile({ label, value, icon, tint }) {
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
                    <Icon source={icon} />
                </div>
                <BlockStack gap="050">
                    <Text as="span" variant="bodySm" tone="subdued">{label}</Text>
                    <Text as="span" variant="headingLg">{value}</Text>
                </BlockStack>
            </InlineStack>
        </Card>
    );
}

function RankedList({ items, renderLabel, renderValue, emptyText }) {
    if (items.length === 0) {
        return (
            <Box paddingBlockStart="200">
                <Text as="p" tone="subdued">{emptyText}</Text>
            </Box>
        );
    }

    return (
        <BlockStack gap="0">
            {items.map((item, i) => (
                <React.Fragment key={item.id}>
                    {i > 0 && <Divider />}
                    <Box paddingBlock="250">
                        <InlineStack align="space-between" blockAlign="center" gap="200">
                            <InlineStack gap="200" blockAlign="center">
                                <div style={{
                                    width: '22px',
                                    height: '22px',
                                    borderRadius: '6px',
                                    background: i < 3 ? '#e8f5f0' : '#f6f7f9',
                                    color: i < 3 ? '#00543d' : '#6b7280',
                                    fontSize: '11px',
                                    fontWeight: 700,
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    flex: 'none',
                                }}>
                                    {i + 1}
                                </div>
                                <Text as="span">{renderLabel(item)}</Text>
                            </InlineStack>
                            <Text as="span" tone="subdued">{renderValue(item)}</Text>
                        </InlineStack>
                    </Box>
                </React.Fragment>
            ))}
        </BlockStack>
    );
}

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
    const labelFormatter = period === 'day'
        ? (d) => new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
        : (d) => new Date(`${d}-01`).toLocaleDateString(undefined, { month: 'short', year: '2-digit' });

    return (
        <Page title="Analytics">
            <BlockStack gap="400">
                <InlineGrid columns={{ xs: 1, sm: 2 }} gap="400">
                    <StatTile label="Total downloads" value={data.total_downloads} icon={ArrowDownIcon} tint="#f3edfb" />
                    <StatTile label="Storage used" value={formatBytes(data.storage_usage_bytes)} icon={DatabaseIcon} tint="#fdf3e7" />
                </InlineGrid>

                <Card>
                    <BlockStack gap="300">
                        <InlineStack align="space-between" blockAlign="center">
                            <InlineStack gap="200" blockAlign="center">
                                <Icon source={ChartVerticalIcon} tone="subdued" />
                                <Text as="h2" variant="headingMd">Downloads over time</Text>
                            </InlineStack>
                            <InlineStack gap="0">
                                <Button size="slim" pressed={period === 'day'} onClick={() => setPeriod('day')}>Day</Button>
                                <Button size="slim" pressed={period === 'month'} onClick={() => setPeriod('month')}>Month</Button>
                            </InlineStack>
                        </InlineStack>
                        {series.length === 0 ? (
                            <Text as="p" tone="subdued">No downloads in this range yet.</Text>
                        ) : (
                            <BarChart data={series} labelFormatter={labelFormatter} />
                        )}
                    </BlockStack>
                </Card>

                <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
                    <Card>
                        <Text as="h2" variant="headingMd">Top downloaded products</Text>
                        <RankedList
                            items={data.top_downloaded_products}
                            renderLabel={(p) => p.shopify_product_title}
                            renderValue={(p) => `${p.downloads_count} downloads`}
                            emptyText="No downloads yet."
                        />
                    </Card>

                    <Card>
                        <Text as="h2" variant="headingMd">Downloads per order</Text>
                        <RankedList
                            items={data.downloads_per_order}
                            renderLabel={(o) => `#${o.shopify_order_number}`}
                            renderValue={(o) => `${o.downloads_count} downloads`}
                            emptyText="No downloads yet."
                        />
                    </Card>
                </InlineGrid>

                <Card>
                    <InlineStack gap="200" blockAlign="center">
                        <Icon source={ReceiptIcon} tone="subdued" />
                        <Text as="h2" variant="headingMd">Recent activity</Text>
                    </InlineStack>
                    {data.recent_activity.length === 0 ? (
                        <Box paddingBlockStart="200">
                            <Text as="p" tone="subdued">No activity yet.</Text>
                        </Box>
                    ) : (
                        <BlockStack gap="0">
                            {data.recent_activity.map((item, i) => (
                                <React.Fragment key={i}>
                                    {i > 0 && <Divider />}
                                    <Box paddingBlock="250">
                                        <InlineStack align="space-between" blockAlign="center">
                                            <Text as="span">{item.file_name}</Text>
                                            <InlineStack gap="200">
                                                <Text as="span" tone="subdued">Order #{item.order_number}</Text>
                                                <Text as="span" tone="subdued">{new Date(item.downloaded_at).toLocaleString()}</Text>
                                            </InlineStack>
                                        </InlineStack>
                                    </Box>
                                </React.Fragment>
                            ))}
                        </BlockStack>
                    )}
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
