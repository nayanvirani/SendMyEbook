import React, { useCallback, useEffect, useState } from 'react';
import { Page, Card, FormLayout, TextField, Select, Banner, Button, BlockStack, Text, InlineStack } from '@shopify/polaris';
import { api } from '../api';

export default function Settings() {
    const [form, setForm] = useState(null);
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState(false);
    const [testEmail, setTestEmail] = useState('');
    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState(null);

    useEffect(() => {
        api.get('/settings').then(setForm).catch(() => {});
    }, []);

    const field = (key) => (value) => setForm((f) => ({ ...f, [key]: value }));

    const handleSave = useCallback(async () => {
        setSaving(true);
        setSaved(false);
        const saved = await api.put('/settings', form);
        // The password never comes back from the server — don't leave the
        // just-typed value sitting in state implying it's still "unsaved".
        setForm({ ...saved, smtp_password: '' });
        setSaving(false);
        setSaved(true);
    }, [form]);

    const handleTestEmail = useCallback(async () => {
        setTesting(true);
        setTestResult(null);
        try {
            // Save first so the test actually exercises what's currently
            // typed, not whatever was saved last.
            await handleSave();
            await api.post('/settings/test-email', { to: testEmail });
            setTestResult({ tone: 'success', message: `Test email sent to ${testEmail}.` });
        } catch (e) {
            setTestResult({ tone: 'critical', message: e.body?.error || 'Could not send the test email.' });
        } finally {
            setTesting(false);
        }
    }, [testEmail, handleSave]);

    if (!form) return null;

    return (
        <Page title="Settings" primaryAction={{ content: 'Save', loading: saving, onAction: handleSave }}>
            <BlockStack gap="400">
                {saved && <Banner tone="success" onDismiss={() => setSaved(false)}>Settings saved.</Banner>}

                <Card>
                    <FormLayout>
                        <Text as="h2" variant="headingMd">General</Text>
                        <TextField
                            label="Email 'from' name"
                            value={form.email_from_name || ''}
                            onChange={field('email_from_name')}
                            autoComplete="off"
                        />
                        <TextField
                            label="Support email"
                            type="email"
                            value={form.support_email || ''}
                            onChange={field('support_email')}
                            autoComplete="off"
                        />
                        <TextField
                            label="Default maximum downloads"
                            type="number"
                            value={String(form.default_max_downloads ?? '')}
                            onChange={field('default_max_downloads')}
                            autoComplete="off"
                        />
                        <TextField
                            label="Default link expiration (days)"
                            type="number"
                            value={String(form.default_expiration_days ?? '')}
                            onChange={field('default_expiration_days')}
                            autoComplete="off"
                        />
                    </FormLayout>
                </Card>

                <Card>
                    <BlockStack gap="300">
                        <BlockStack gap="100">
                            <Text as="h2" variant="headingMd">Branding</Text>
                            <Text as="p" tone="subdued">
                                Shown on the customer download page and in delivery emails instead of the
                                default plain look.
                            </Text>
                        </BlockStack>

                        <FormLayout>
                            <TextField
                                label="Logo URL"
                                placeholder="https://yourstore.com/logo.png"
                                value={form.logo_url || ''}
                                onChange={field('logo_url')}
                                autoComplete="off"
                            />
                            <TextField
                                label="Brand color"
                                placeholder="#008060"
                                value={form.brand_color || ''}
                                onChange={field('brand_color')}
                                autoComplete="off"
                                connectedRight={
                                    <div
                                        style={{
                                            width: '2.25rem',
                                            height: '2.25rem',
                                            borderRadius: 'var(--p-border-radius-200)',
                                            border: '1px solid var(--p-color-border)',
                                            background: /^#[0-9a-fA-F]{6}$/.test(form.brand_color || '')
                                                ? form.brand_color
                                                : 'transparent',
                                        }}
                                    />
                                }
                            />
                        </FormLayout>
                    </BlockStack>
                </Card>

                <Card>
                    <BlockStack gap="300">
                        <BlockStack gap="100">
                            <Text as="h2" variant="headingMd">Email delivery</Text>
                            <Text as="p" tone="subdued">
                                By default, delivery emails are sent from SendMyEbook's own account. Add your own
                                SMTP provider (Gmail, SendGrid, Mailgun, your own server — any of them) to send
                                from your own domain instead.
                            </Text>
                        </BlockStack>

                        <FormLayout>
                            <TextField
                                label="From email address"
                                type="email"
                                placeholder="downloads@yourstore.com"
                                value={form.mail_from_address || ''}
                                onChange={field('mail_from_address')}
                                autoComplete="off"
                            />
                            <FormLayout.Group>
                                <TextField
                                    label="SMTP host"
                                    placeholder="smtp.example.com"
                                    value={form.smtp_host || ''}
                                    onChange={field('smtp_host')}
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Port"
                                    type="number"
                                    placeholder="587"
                                    value={String(form.smtp_port ?? '')}
                                    onChange={field('smtp_port')}
                                    autoComplete="off"
                                />
                            </FormLayout.Group>
                            <FormLayout.Group>
                                <TextField
                                    label="Username"
                                    value={form.smtp_username || ''}
                                    onChange={field('smtp_username')}
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Password"
                                    type="password"
                                    placeholder={form.has_smtp_password ? 'Saved — leave blank to keep it' : ''}
                                    value={form.smtp_password || ''}
                                    onChange={field('smtp_password')}
                                    autoComplete="off"
                                />
                            </FormLayout.Group>
                            <Select
                                label="Encryption"
                                options={[
                                    { label: 'None', value: '' },
                                    { label: 'TLS', value: 'tls' },
                                    { label: 'SSL', value: 'ssl' },
                                ]}
                                value={form.smtp_encryption || ''}
                                onChange={field('smtp_encryption')}
                            />
                        </FormLayout>

                        <InlineStack gap="200" blockAlign="end">
                            <TextField
                                label="Send a test email to"
                                type="email"
                                placeholder="you@example.com"
                                value={testEmail}
                                onChange={setTestEmail}
                                autoComplete="off"
                            />
                            <Button onClick={handleTestEmail} loading={testing} disabled={!testEmail}>
                                Send test
                            </Button>
                        </InlineStack>
                        {testResult && (
                            <Banner tone={testResult.tone} onDismiss={() => setTestResult(null)}>
                                {testResult.message}
                            </Banner>
                        )}
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
}
