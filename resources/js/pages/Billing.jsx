import React, { useEffect, useState } from 'react';
import { Page, Card, BlockStack, InlineGrid, Text, Button, Banner } from '@shopify/polaris';
import { api } from '../api';

export default function Billing() {
    const [plans, setPlans] = useState(null);
    const [subscribing, setSubscribing] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        api.get('/billing/plans').then(setPlans);
    }, []);

    const handleSubscribe = async (plan) => {
        setSubscribing(plan.id);
        setError(null);
        try {
            const { confirmation_url } = await api.post('/billing/subscribe', { plan_id: plan.id });
            // Billing approval must happen outside the embedded iframe.
            window.open(confirmation_url, '_top');
        } catch (e) {
            setError('Could not start the subscription. Please try again.');
        } finally {
            setSubscribing(null);
        }
    };

    return (
        <Page title="Billing">
            {error && <Banner tone="critical" onDismiss={() => setError(null)}>{error}</Banner>}
            <InlineGrid columns={{ xs: 1, sm: 3 }} gap="400">
                {(plans || []).map((plan) => (
                    <Card key={plan.id}>
                        <BlockStack gap="300">
                            <Text as="h2" variant="headingMd">{plan.name}</Text>
                            <Text as="span" variant="heading2xl">${plan.price}<Text as="span" tone="subdued">/mo</Text></Text>
                            <Text as="p" tone="subdued">
                                {plan.max_digital_products ? `${plan.max_digital_products} digital products` : 'Unlimited digital products'}
                            </Text>
                            <Button
                                variant="primary"
                                loading={subscribing === plan.id}
                                onClick={() => handleSubscribe(plan)}
                            >
                                Choose plan
                            </Button>
                        </BlockStack>
                    </Card>
                ))}
            </InlineGrid>
        </Page>
    );
}
