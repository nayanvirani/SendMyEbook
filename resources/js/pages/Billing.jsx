import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, InlineGrid, Text, Button, Banner } from '@shopify/polaris';
import { api } from '../api';

/**
 * Billing is Shopify App Pricing (Managed Pricing) — plans, prices and
 * trials live in the Partner Dashboard, and a merchant picks a plan on
 * Shopify's own hosted page. This page only previews those plans and
 * links out; it never creates a subscription itself (Shopify's Billing
 * API rejects appSubscriptionCreate once App Pricing is enabled).
 */
export default function Billing() {
    const [plans, setPlans] = useState(null);
    const [status, setStatus] = useState(null);

    useEffect(() => {
        api.get('/billing/plans').then(setPlans).catch(() => setPlans([]));
        api.get('/subscription-status').then(setStatus).catch(() => {});
    }, []);

    const openPlanPicker = () => {
        if (status?.manage_plan_url) {
            // Shopify's hosted plan page has to open outside the embedded iframe.
            window.open(status.manage_plan_url, '_top');
        }
    };

    const usage = status?.usage;

    return (
        <Page title="Billing">
            {status?.active && (
                <Banner tone="success">
                    You're on the {status.plan?.name ?? 'current'} plan. Manage or change it on Shopify.
                </Banner>
            )}

            {usage && (
                <div style={{ marginTop: 'var(--p-space-400)' }}>
                    <Card>
                        <BlockStack gap="300">
                            <Text as="h2" variant="headingMd">Usage</Text>
                            <InlineGrid columns={{ xs: 1, sm: 2 }} gap="400">
                                <BlockStack gap="100">
                                    <Text as="span" tone="subdued">Digital products</Text>
                                    <Text as="span" variant="headingMd">
                                        {usage.digital_products.used}
                                        {usage.digital_products.limit ? ` / ${usage.digital_products.limit}` : ' (unlimited)'}
                                    </Text>
                                </BlockStack>
                                <BlockStack gap="100">
                                    <Text as="span" tone="subdued">Downloads this month</Text>
                                    <Text as="span" variant="headingMd">
                                        {usage.downloads_this_month.used}
                                        {usage.downloads_this_month.limit ? ` / ${usage.downloads_this_month.limit}` : ' (unlimited)'}
                                    </Text>
                                </BlockStack>
                            </InlineGrid>
                        </BlockStack>
                    </Card>
                </div>
            )}

            <div style={{ marginTop: 'var(--p-space-400)' }}>
                <InlineGrid columns={{ xs: 1, sm: 3 }} gap="400">
                    {(plans || []).map((plan) => (
                        <Card key={plan.id}>
                            <BlockStack gap="300">
                                <Text as="h2" variant="headingMd">{plan.name}</Text>
                                <Text as="span" variant="heading2xl">${plan.price}<Text as="span" tone="subdued">/mo</Text></Text>
                                <Text as="p" tone="subdued">
                                    {plan.max_digital_products ? `${plan.max_digital_products} digital products` : 'Unlimited digital products'}
                                </Text>
                                <Button variant="primary" onClick={openPlanPicker}>
                                    {status?.plan?.handle === plan.handle ? 'Manage plan' : 'Choose plan'}
                                </Button>
                            </BlockStack>
                        </Card>
                    ))}
                </InlineGrid>
            </div>
        </Page>
    );
}
