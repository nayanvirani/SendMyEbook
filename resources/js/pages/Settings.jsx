import React, { useCallback, useEffect, useState } from 'react';
import { Page, Card, FormLayout, TextField, Banner } from '@shopify/polaris';
import { api } from '../api';

export default function Settings() {
    const [form, setForm] = useState(null);
    const [saving, setSaving] = useState(false);
    const [saved, setSaved] = useState(false);

    useEffect(() => {
        api.get('/settings').then(setForm);
    }, []);

    const field = (key) => (value) => setForm((f) => ({ ...f, [key]: value }));

    const handleSave = useCallback(async () => {
        setSaving(true);
        setSaved(false);
        await api.put('/settings', form);
        setSaving(false);
        setSaved(true);
    }, [form]);

    if (!form) return null;

    return (
        <Page title="Settings" primaryAction={{ content: 'Save', loading: saving, onAction: handleSave }}>
            {saved && <Banner tone="success" onDismiss={() => setSaved(false)}>Settings saved.</Banner>}
            <Card>
                <FormLayout>
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
        </Page>
    );
}
