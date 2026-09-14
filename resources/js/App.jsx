import React, { useEffect, useState } from 'react';
import { Routes, Route } from 'react-router-dom';
import { Frame, Navigation, SkeletonPage, Banner, BlockStack } from '@shopify/polaris';
import { useNavigate, useLocation } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import DigitalProductsIndex from './pages/DigitalProducts/Index';
import DigitalProductForm from './pages/DigitalProducts/Form';
import Orders from './pages/Orders';
import Downloads from './pages/Downloads';
import Analytics from './pages/Analytics';
import Settings from './pages/Settings';
import Billing from './pages/Billing';
import { api } from './api';

const NAV_ITEMS = [
    { label: 'Dashboard', path: '/' },
    { label: 'Digital Products', path: '/digital-products' },
    { label: 'Orders', path: '/orders' },
    { label: 'Downloads', path: '/downloads' },
    { label: 'Analytics', path: '/analytics' },
    { label: 'Settings', path: '/settings' },
    { label: 'Billing', path: '/billing' },
];

export default function App() {
    const navigate = useNavigate();
    const location = useLocation();

    // There is no free plan — a shop with no active subscription can only
    // ever see the paywall below, regardless of which route it's on.
    const [subscriptionActive, setSubscriptionActive] = useState(null);

    useEffect(() => {
        api.get('/subscription-status')
            .then((res) => setSubscriptionActive(res.active))
            .catch(() => setSubscriptionActive(false));
    }, []);

    const navigation = (
        <Navigation location={location.pathname}>
            <Navigation.Section
                items={NAV_ITEMS.map((item) => ({
                    label: item.label,
                    selected: location.pathname === item.path,
                    onClick: () => navigate(item.path),
                }))}
            />
        </Navigation>
    );

    if (subscriptionActive === null) {
        return (
            <Frame navigation={navigation}>
                <SkeletonPage />
            </Frame>
        );
    }

    if (subscriptionActive === false) {
        return (
            <Frame>
                <BlockStack gap="400">
                    <div style={{ padding: '0 var(--p-space-400)', paddingTop: 'var(--p-space-400)' }}>
                        <Banner tone="info">
                            Choose a plan below to start using SendMyEbook — digital product
                            mapping, secure downloads and delivery are unavailable until you
                            subscribe.
                        </Banner>
                    </div>
                    <Billing />
                </BlockStack>
            </Frame>
        );
    }

    return (
        <Frame navigation={navigation}>
            <Routes>
                <Route path="/" element={<Dashboard />} />
                <Route path="/digital-products" element={<DigitalProductsIndex />} />
                <Route path="/digital-products/new" element={<DigitalProductForm />} />
                <Route path="/digital-products/:id" element={<DigitalProductForm />} />
                <Route path="/orders" element={<Orders />} />
                <Route path="/downloads" element={<Downloads />} />
                <Route path="/analytics" element={<Analytics />} />
                <Route path="/settings" element={<Settings />} />
                <Route path="/billing" element={<Billing />} />
            </Routes>
        </Frame>
    );
}
