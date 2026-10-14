import React, { useCallback, useEffect, useState } from 'react';
import { Page, Card, FormLayout, TextField, Banner, Button, BlockStack, Text, InlineStack, Checkbox, Modal, Badge } from '@shopify/polaris';
import { api } from '../api';

export default function Settings() {
    const [form, setForm] = useState(null);
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState(false);
    const [testEmail, setTestEmail] = useState('');
    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState(null);
    const [previewHtml, setPreviewHtml] = useState(null);
    const [loadingPreview, setLoadingPreview] = useState(false);
    const [canBrand, setCanBrand] = useState(true);
    const [maxDownloadsCeiling, setMaxDownloadsCeiling] = useState(null);

    useEffect(() => {
        api.get('/settings').then(setForm).catch(() => {});
        api.get('/subscription-status').then((s) => {
            setCanBrand(!!s.features?.custom_branding);
            setMaxDownloadsCeiling(s.limits?.max_downloads_per_product ?? null);
        }).catch(() => {});
    }, []);

    const field = (key) => (value) => setForm((f) => ({ ...f, [key]: value }));

    const handleSave = useCallback(async () => {
        setSaving(true);
        setSaved(false);
        const saved = await api.put('/settings', form);
        // Neither secret comes back from the server — don't leave the
        // just-typed value sitting in state implying it's still "unsaved".
        setForm({ ...saved, smtp_password: '', resend_api_key: '' });
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

    const handlePreviewEmail = useCallback(async () => {
        setLoadingPreview(true);
        try {
            // Save first so the preview reflects the logo/color/support
            // email currently typed, not whatever was saved last.
            await handleSave();
            const { html } = await api.get('/settings/email-preview');
            setPreviewHtml(html);
        } catch (e) {
            setTestResult({ tone: 'critical', message: 'Could not load the email preview.' });
        } finally {
            setLoadingPreview(false);
        }
    }, [handleSave]);

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
                            max={maxDownloadsCeiling || undefined}
                            helpText={maxDownloadsCeiling
                                ? `Applied to any product that doesn't set its own limit. Your plan allows up to ${maxDownloadsCeiling}.`
                                : "Applied to any product that doesn't set its own limit. Leave blank for unlimited."}
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
                            <InlineStack gap="200" blockAlign="center">
                                <Text as="h2" variant="headingMd">Branding</Text>
                                {!canBrand && <Badge tone="info">Pro feature</Badge>}
                            </InlineStack>
                            <Text as="p" tone="subdued">
                                {canBrand
                                    ? 'Shown on the customer download page and in delivery emails instead of the default plain look.'
                                    : 'Upgrade to Pro to customize your logo and brand color on the download page and delivery emails.'}
                            </Text>
                        </BlockStack>

                        <FormLayout>
                            <TextField
                                label="Logo URL"
                                placeholder="https://yourstore.com/logo.png"
                                value={form.logo_url || ''}
                                onChange={field('logo_url')}
                                autoComplete="off"
                                disabled={!canBrand}
                            />
                            <TextField
                                label="Brand color"
                                placeholder="#008060"
                                value={form.brand_color || ''}
                                onChange={field('brand_color')}
                                autoComplete="off"
                                disabled={!canBrand}
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
                                Delivery emails are sent automatically through SendMyEbook. Turn them off here if you'd rather customers only use the download link on the Thank You page.
                            </Text>
                        </BlockStack>

                        <Checkbox
                            label="Enable email sending"
                            helpText={
                                form.mail_enabled
                                    ? 'Delivery emails will be sent after each order.'
                                    : 'No delivery emails will be sent at all — customers will only see the download link on the Thank You page.'
                            }
                            checked={!!form.mail_enabled}
                            onChange={field('mail_enabled')}
                        />

                        <TextField
                            label="From email address (optional)"
                            type="email"
                            helpText="Overrides the sender address shown to customers; the email still sends through SendMyEbook's own account."
                            placeholder="downloads@yourstore.com"
                            value={form.mail_from_address || ''}
                            onChange={field('mail_from_address')}
                            autoComplete="off"
                            disabled={!form.mail_enabled}
                        />

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
                            <Button onClick={handlePreviewEmail} loading={loadingPreview}>
                                Preview email
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

            <Modal
                open={previewHtml !== null}
                onClose={() => setPreviewHtml(null)}
                title="Delivery email preview"
                size="large"
            >
                <Modal.Section flush>
                    {previewHtml && (
                        <iframe
                            title="Email preview"
                            srcDoc={previewHtml}
                            style={{ width: '100%', height: '70vh', border: 'none', display: 'block' }}
                        />
                    )}
                </Modal.Section>
            </Modal>
        </Page>
    );
}
