import React from 'react';
import { Routes, Route } from 'react-router-dom';
import { Frame, Navigation } from '@shopify/polaris';
import { useNavigate, useLocation } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import DigitalProductsIndex from './pages/DigitalProducts/Index';
import DigitalProductForm from './pages/DigitalProducts/Form';
import Orders from './pages/Orders';
import Downloads from './pages/Downloads';
import Analytics from './pages/Analytics';
import Settings from './pages/Settings';
import Billing from './pages/Billing';

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
