import React, { useEffect, useState } from 'react';
import { Page, Card, IndexTable, EmptyState } from '@shopify/polaris';
import { api } from '../api';

export default function Downloads() {
    const [downloads, setDownloads] = useState(null);

    useEffect(() => {
        api.get('/downloads').then((res) => setDownloads(res.data)).catch(() => setDownloads([]));
    }, []);

    return (
        <Page title="Downloads">
            <Card padding="0">
                {downloads && downloads.length === 0 ? (
                    <EmptyState heading="No download activity yet">
                        <p>Customer downloads will be logged here.</p>
                    </EmptyState>
                ) : (
                    <IndexTable
                        resourceName={{ singular: 'download', plural: 'downloads' }}
                        itemCount={downloads ? downloads.length : 0}
                        loading={!downloads}
                        selectable={false}
                        headings={[
                            { title: 'File' },
                            { title: 'Order' },
                            { title: 'Product' },
                            { title: 'IP address' },
                            { title: 'When' },
                        ]}
                    >
                        {(downloads || []).map((download, index) => (
                            <IndexTable.Row id={String(download.id)} key={download.id} position={index}>
                                <IndexTable.Cell>{download.file_name}</IndexTable.Cell>
                                <IndexTable.Cell>#{download.order_number}</IndexTable.Cell>
                                <IndexTable.Cell>{download.digital_product_title}</IndexTable.Cell>
                                <IndexTable.Cell>{download.ip_address ?? '—'}</IndexTable.Cell>
                                <IndexTable.Cell>{new Date(download.downloaded_at).toLocaleString()}</IndexTable.Cell>
                            </IndexTable.Row>
                        ))}
                    </IndexTable>
                )}
            </Card>
        </Page>
    );
}
