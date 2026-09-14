import React, { useEffect, useState } from 'react';
import { Page, Card, IndexTable, Badge, EmptyState } from '@shopify/polaris';
import { api } from '../api';

export default function Orders() {
    const [orders, setOrders] = useState(null);

    useEffect(() => {
        api.get('/orders').then((res) => setOrders(res.data));
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
                            { title: 'Status' },
                            { title: 'Total' },
                        ]}
                    >
                        {(orders || []).map((order, index) => (
                            <IndexTable.Row id={String(order.id)} key={order.id} position={index}>
                                <IndexTable.Cell>#{order.shopify_order_number}</IndexTable.Cell>
                                <IndexTable.Cell>{order.customer_email ?? '—'}</IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Badge tone={order.is_refunded ? 'critical' : 'success'}>
                                        {order.financial_status ?? 'unknown'}
                                    </Badge>
                                </IndexTable.Cell>
                                <IndexTable.Cell>{order.currency} {order.total_price}</IndexTable.Cell>
                            </IndexTable.Row>
                        ))}
                    </IndexTable>
                )}
            </Card>
        </Page>
    );
}
