import React, { useCallback, useEffect, useState } from 'react';
import {
    Page,
    Card,
    FormLayout,
    TextField,
    Select,
    Checkbox,
    Button,
    BlockStack,
    InlineStack,
    Text,
    DropZone,
    Banner,
} from '@shopify/polaris';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api';
import { uploadFile } from '../../uploadFile';

const emptyForm = {
    shopify_product_id: '',
    shopify_product_title: '',
    shopify_variant_id: '',
    status: 'active',
    max_downloads: '',
    expiration_value: '',
    expiration_unit: 'days',
    revoke_on_refund: true,
};

export default function DigitalProductForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEditing = Boolean(id);

    const [form, setForm] = useState(emptyForm);
    const [files, setFiles] = useState([]);
    const [saving, setSaving] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!isEditing) return;
        api.get(`/digital-products/${id}`).then((product) => {
            setForm({
                shopify_product_id: product.shopify_product_id,
                shopify_product_title: product.shopify_product_title || '',
                shopify_variant_id: product.shopify_variant_id || '',
                status: product.status,
                max_downloads: product.max_downloads ?? '',
                expiration_value: product.expiration_value ?? '',
                expiration_unit: product.expiration_unit,
                revoke_on_refund: product.revoke_on_refund,
            });
            setFiles(product.files || []);
        });
    }, [id, isEditing]);

    const field = (key) => (value) => setForm((f) => ({ ...f, [key]: value }));

    const handleSave = useCallback(async () => {
        setSaving(true);
        setError(null);
        try {
            const payload = {
                ...form,
                max_downloads: form.max_downloads === '' ? null : Number(form.max_downloads),
                expiration_value: form.expiration_value === '' ? null : Number(form.expiration_value),
                shopify_variant_id: form.shopify_variant_id || null,
            };

            if (isEditing) {
                await api.put(`/digital-products/${id}`, payload);
            } else {
                const created = await api.post('/digital-products', payload);
                navigate(`/digital-products/${created.id}`, { replace: true });
                return;
            }
            navigate('/digital-products');
        } catch (e) {
            setError(e.body?.message || 'Could not save this digital product.');
        } finally {
            setSaving(false);
        }
    }, [form, id, isEditing, navigate]);

    const handleDrop = useCallback(async (_dropped, accepted) => {
        if (!isEditing) {
            setError('Save the digital product before uploading files.');
            return;
        }
        setUploading(true);
        try {
            for (const file of accepted) {
                const uploaded = await uploadFile(id, file);
                setFiles((prev) => [...prev, uploaded]);
            }
        } catch (e) {
            setError('File upload failed. Please try again.');
        } finally {
            setUploading(false);
        }
    }, [id, isEditing]);

    const removeFile = async (fileId) => {
        await api.delete(`/files/${fileId}`);
        setFiles((prev) => prev.filter((f) => f.id !== fileId));
    };

    return (
        <Page
            title={isEditing ? 'Edit Digital Product' : 'Add Digital Product'}
            backAction={{ content: 'Digital Products', onAction: () => navigate('/digital-products') }}
            primaryAction={{ content: 'Save', loading: saving, onAction: handleSave }}
        >
            <BlockStack gap="400">
                {error && <Banner tone="critical" onDismiss={() => setError(null)}>{error}</Banner>}

                <Card>
                    <FormLayout>
                        <Text as="h2" variant="headingMd">Shopify product</Text>
                        <TextField
                            label="Shopify Product ID"
                            helpText="The numeric product ID from Shopify Admin (a live product picker is a good next iteration)."
                            value={form.shopify_product_id}
                            onChange={field('shopify_product_id')}
                            autoComplete="off"
                        />
                        <TextField
                            label="Product title (for display only)"
                            value={form.shopify_product_title}
                            onChange={field('shopify_product_title')}
                            autoComplete="off"
                        />
                        <TextField
                            label="Variant ID (optional)"
                            helpText="Leave blank to map the whole product."
                            value={form.shopify_variant_id}
                            onChange={field('shopify_variant_id')}
                            autoComplete="off"
                        />
                        <Select
                            label="Status"
                            options={[{ label: 'Active', value: 'active' }, { label: 'Inactive', value: 'inactive' }]}
                            value={form.status}
                            onChange={field('status')}
                        />
                    </FormLayout>
                </Card>

                <Card>
                    <FormLayout>
                        <Text as="h2" variant="headingMd">Download rules</Text>
                        <TextField
                            label="Maximum downloads"
                            type="number"
                            helpText="Leave blank for unlimited."
                            value={String(form.max_downloads)}
                            onChange={field('max_downloads')}
                            autoComplete="off"
                        />
                        <InlineStack gap="200">
                            <TextField
                                label="Link expiration"
                                type="number"
                                helpText="Leave blank for a link that never expires."
                                value={String(form.expiration_value)}
                                onChange={field('expiration_value')}
                                autoComplete="off"
                            />
                            <Select
                                label="Unit"
                                options={[{ label: 'Hours', value: 'hours' }, { label: 'Days', value: 'days' }]}
                                value={form.expiration_unit}
                                onChange={field('expiration_unit')}
                            />
                        </InlineStack>
                        <Checkbox
                            label="Disable download access after a refund"
                            checked={form.revoke_on_refund}
                            onChange={field('revoke_on_refund')}
                        />
                    </FormLayout>
                </Card>

                <Card>
                    <BlockStack gap="300">
                        <Text as="h2" variant="headingMd">Files</Text>
                        <DropZone onDrop={handleDrop} disabled={uploading}>
                            <DropZone.FileUpload actionTitle={uploading ? 'Uploading…' : 'Add files'} />
                        </DropZone>
                        {files.map((file) => (
                            <InlineStack key={file.id} align="space-between" blockAlign="center">
                                <Text as="span">{file.original_filename} ({file.status})</Text>
                                <Button variant="plain" tone="critical" onClick={() => removeFile(file.id)}>
                                    Remove
                                </Button>
                            </InlineStack>
                        ))}
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
}
