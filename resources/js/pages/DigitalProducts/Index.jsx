import React, { useEffect, useState } from 'react';
import { Page, Card, IndexTable, Badge, EmptyState, useIndexResourceState } from '@shopify/polaris';
import { useNavigate } from 'react-router-dom';
import { api } from '../../api';

export default function DigitalProductsIndex() {
    const navigate = useNavigate();
    const [products, setProducts] = useState(null);

    useEffect(() => {
        api.get('/digital-products').then((res) => setProducts(res.data));
    }, []);

    const { selectedResources, allResourcesSelected, handleSelectionChange } = useIndexResourceState(products || []);

    return (
        <Page
            title="Digital Products"
            primaryAction={{ content: 'Add Digital Product', onAction: () => navigate('/digital-products/new') }}
        >
            <Card padding="0">
                {products && products.length === 0 ? (
                    <EmptyState
                        heading="No digital products yet"
                        action={{ content: 'Add Digital Product', onAction: () => navigate('/digital-products/new') }}
                    >
                        <p>Map a Shopify product to one or more files to start delivering downloads.</p>
                    </EmptyState>
                ) : (
                    <IndexTable
                        resourceName={{ singular: 'digital product', plural: 'digital products' }}
                        itemCount={products ? products.length : 0}
                        loading={!products}
                        selectedItemsCount={allResourcesSelected ? 'All' : selectedResources.length}
                        onSelectionChange={handleSelectionChange}
                        headings={[
                            { title: 'Product' },
                            { title: 'Status' },
                            { title: 'Files' },
                            { title: 'Max downloads' },
                            { title: 'Expiration' },
                        ]}
                    >
                        {(products || []).map((product, index) => (
                            <IndexTable.Row
                                id={String(product.id)}
                                key={product.id}
                                position={index}
                                onClick={() => navigate(`/digital-products/${product.id}`)}
                            >
                                <IndexTable.Cell>{product.shopify_product_title || product.shopify_product_id}</IndexTable.Cell>
                                <IndexTable.Cell>
                                    <Badge tone={product.status === 'active' ? 'success' : 'default'}>
                                        {product.status}
                                    </Badge>
                                </IndexTable.Cell>
                                <IndexTable.Cell>{product.files_count ?? 0}</IndexTable.Cell>
                                <IndexTable.Cell>{product.max_downloads ?? 'Unlimited'}</IndexTable.Cell>
                                <IndexTable.Cell>
                                    {product.expiration_value ? `${product.expiration_value} ${product.expiration_unit}` : 'Never'}
                                </IndexTable.Cell>
                            </IndexTable.Row>
                        ))}
                    </IndexTable>
                )}
            </Card>
        </Page>
    );
}
