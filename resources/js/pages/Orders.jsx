import React, { useEffect, useState } from 'react';
import { Page, Card, IndexTable, Badge, EmptyState, BlockStack, InlineStack, Text } from '@shopify/polaris';
import { api } from '../api';

const STATUS_TONE = {
    active: 'success',
    expired: 'warning',
    limit_reached: 'attention',
    revoked: 'critical',
};

function statusLabel(status) {
    return {
        active: 'Active',
        expired: 'Expired',
        limit_reached: 'Limit reached',
        revoked: 'Revoked',
    }[status] ?? status;
}

export default function Orders() {
    const [orders, setOrders] = useState(null);

    useEffect(() => {
        api.get('/orders').then((res) => setOrders(res.data)).catch(() => setOrders([]));
    }, []);

    return (
        <Page title="Orders">
            <Card padding="0">
                {orders && orders.length === 0 ? (
                    <EmptyState heading="No orders yet">
                        <p>Orders with a mapped digital product will appear here after checkout.</p>
                    </EmptyState>
                ) : (
                    <IndexTable
                        resourceName={{ singular: 'order', plural: 'orders' }}
                        itemCount={orders ? orders.length : 0}
                        loading={!orders}
                        selectable={false}
                        headings={[
                            { title: 'Order' },
                            { title: 'Customer' },
                            { title: 'Payment status' },
                            { title: 'Total' },
                            { title: 'Digital products' },
                            { title: 'Downloads' },
                            { title: 'Delivery status' },
                            { title: 'Last download' },
                        ]}
                    >
                        {(orders || []).map((order, index) => {
                            const tokens = order.download_tokens || [];

                            return (
                                <IndexTable.Row id={String(order.id)} key={order.id} position={index}>
                                    <IndexTable.Cell>#{order.shopify_order_number}</IndexTable.Cell>
                                    <IndexTable.Cell>{order.customer_email ?? '—'}</IndexTable.Cell>
                                    <IndexTable.Cell>
                                        <InlineStack gap="100">
                                            <Badge tone={order.is_refunded ? 'critical' : 'success'}>
                                                {order.financial_status ?? 'unknown'}
                                            </Badge>
                                            {order.is_cancelled && <Badge tone="critical">Cancelled</Badge>}
                                            {order.is_closed && <Badge tone="subdued">Archived</Badge>}
                                        </InlineStack>
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>{order.currency} {order.total_price}</IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {tokens.length === 0 ? (
                                            '—'
                                        ) : (
                                            <BlockStack gap="100">
                                                {tokens.map((token) => (
                                                    <Text as="span" key={token.id}>{token.digital_product_title}</Text>
                                                ))}
                                            </BlockStack>
                                        )}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {tokens.length === 0 ? (
                                            '—'
                                        ) : (
                                            <BlockStack gap="100">
                                                {tokens.map((token) => (
                                                    <Text as="span" key={token.id}>
                                                        {token.download_count} / {token.max_downloads ?? '∞'}
                                                    </Text>
                                                ))}
                                            </BlockStack>
                                        )}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {tokens.length === 0 ? (
                                            '—'
                                        ) : (
                                            <BlockStack gap="100">
                                                {tokens.map((token) => (
                                                    <Badge key={token.id} tone={STATUS_TONE[token.status] ?? 'subdued'}>
                                                        {statusLabel(token.status)}
                                                    </Badge>
                                                ))}
                                            </BlockStack>
                                        )}
                                    </IndexTable.Cell>
                                    <IndexTable.Cell>
                                        {tokens.length === 0 ? (
                                            '—'
                                        ) : (
                                            <BlockStack gap="100">
                                                {tokens.map((token) => (
                                                    <Text as="span" key={token.id} tone="subdued">
                                                        {token.last_downloaded_at
                                                            ? new Date(token.last_downloaded_at).toLocaleString()
                                                            : 'Never'}
                                                    </Text>
                                                ))}
                                            </BlockStack>
                                        )}
                                    </IndexTable.Cell>
                                </IndexTable.Row>
                            );
                        })}
                    </IndexTable>
                )}
            </Card>
        </Page>
    );
}
