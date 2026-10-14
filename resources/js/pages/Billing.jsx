import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, InlineGrid, InlineStack, Text, Button, Banner, Badge, Box, Icon } from '@shopify/polaris';
import { CheckIcon } from '@shopify/polaris-icons';
import { api } from '../api';

const PLAN_BULLETS = {
    free: [
        '30 ebook deliveries per month',
        'Unlimited ebook titles/products listed',
        'Automatic email delivery after purchase',
        'Download link on Thank You page',
        'Upload up to 250MB per file',
        'Basic file hosting (ePub, PDF, MOBI)',
    ],
    pro: [
        'Unlimited ebook deliveries per month',
        'Upload up to 10GB per file',
        'PDF watermarking (protects against sharing/piracy)',
        'Custom branding (logo, colors on delivery page/emails)',
        'Download analytics (track opens, downloads, repeat buyers)',
        'Priority email support',
    ],
};

function UsageBar({ label, used, limit }) {
    const pct = limit ? Math.min(100, (used / limit) * 100) : 0;
    const nearLimit = limit && used / limit >= 0.9;

    return (
        <BlockStack gap="150">
            <InlineStack align="space-between">
                <Text as="span" tone="subdued">{label}</Text>
                <Text as="span" fontWeight="medium">
                    {used}{limit ? ` / ${limit}` : ' (unlimited)'}
                </Text>
            </InlineStack>
            {limit ? (
                <div style={{ height: '8px', borderRadius: '999px', background: '#f1f2f4', overflow: 'hidden' }}>
                    <div style={{
                        height: '100%',
                        width: `${Math.max(3, pct)}%`,
                        borderRadius: '999px',
                        background: nearLimit ? '#d72c0d' : '#008060',
                        transition: 'width .2s ease',
                    }} />
                </div>
            ) : (
                <div style={{ height: '8px', borderRadius: '999px', background: '#e8f5f0' }} />
            )}
        </BlockStack>
    );
}

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
            <BlockStack gap="400">
                {status?.active && (
                    <Banner tone="success">
                        You're on the {status.plan?.name ?? 'current'} plan. Manage or change it on Shopify.
                    </Banner>
                )}

                {usage && (
                    <Card>
                        <BlockStack gap="400">
                            <Text as="h2" variant="headingMd">Usage</Text>
                            <InlineGrid columns={{ xs: 1, sm: 2 }} gap="400">
                                <UsageBar label="Digital products" used={usage.digital_products.used} limit={usage.digital_products.limit} />
                                <UsageBar label="Email deliveries this month" used={usage.deliveries_this_month.used} limit={usage.deliveries_this_month.limit} />
                            </InlineGrid>
                        </BlockStack>
                    </Card>
                )}

                <InlineGrid columns={{ xs: 1, sm: 2 }} gap="400">
                    {(plans || []).map((plan) => {
                        const isCurrent = status?.plan?.handle === plan.handle;
                        const bullets = PLAN_BULLETS[plan.handle] || [];

                        return (
                            <Box
                                key={plan.id}
                                borderRadius="300"
                                borderWidth={isCurrent ? '025' : undefined}
                                borderColor={isCurrent ? 'border-success' : undefined}
                            >
                                <Card>
                                    <BlockStack gap="300">
                                        <InlineStack align="space-between" blockAlign="center">
                                            <Text as="h2" variant="headingMd">{plan.name}</Text>
                                            {isCurrent && <Badge tone="success">Current plan</Badge>}
                                        </InlineStack>
                                        <InlineStack gap="150" blockAlign="baseline">
                                            <Text as="span" variant="heading2xl">
                                                {Number(plan.price) === 0 ? 'Free' : `$${Number(plan.price).toFixed(0)}`}
                                            </Text>
                                            {Number(plan.price) > 0 && <Text as="span" tone="subdued">/month</Text>}
                                        </InlineStack>
                                        {plan.trial_days && (
                                            <Badge tone="info">{`${plan.trial_days}-day free trial`}</Badge>
                                        )}
                                        <BlockStack gap="150">
                                            {bullets.map((bullet) => (
                                                <InlineStack key={bullet} gap="150" blockAlign="start" wrap={false}>
                                                    <Icon source={CheckIcon} tone="success" />
                                                    <Text as="span">{bullet}</Text>
                                                </InlineStack>
                                            ))}
                                        </BlockStack>
                                        <Button variant={isCurrent ? 'secondary' : 'primary'} onClick={openPlanPicker}>
                                            {isCurrent ? 'Manage plan' : 'Choose plan'}
                                        </Button>
                                    </BlockStack>
                                </Card>
                            </Box>
                        );
                    })}
                </InlineGrid>
            </BlockStack>
        </Page>
    );
}
