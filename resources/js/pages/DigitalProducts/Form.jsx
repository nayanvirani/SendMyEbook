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
    Thumbnail,
    Badge,
} from '@shopify/polaris';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../../api';
import { uploadFile } from '../../uploadFile';

const emptyForm = {
    shopify_product_id: '',
    shopify_product_title: '',
    shopify_variant_id: '',
    shopify_variant_title: '',
    status: 'active',
    max_downloads: '',
    expiration_value: '',
    expiration_unit: 'days',
    revoke_on_refund: true,
    requires_license_key: false,
    watermark_pdfs: false,
};

// Shopify's resource picker returns GraphQL IDs like
// "gid://shopify/Product/123" — the rest of this app works in the plain
// numeric IDs Shopify's REST-shaped webhooks use, so unwrap them here.
function numericId(gid) {
    if (!gid) return '';
    return gid.split('/').pop();
}

export default function DigitalProductForm() {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEditing = Boolean(id);

    const [form, setForm] = useState(emptyForm);
    const [productImage, setProductImage] = useState(null);
    const [variantOptions, setVariantOptions] = useState([]);
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
                shopify_variant_title: product.shopify_variant_title || '',
                status: product.status,
                max_downloads: product.max_downloads ?? '',
                expiration_value: product.expiration_value ?? '',
                expiration_unit: product.expiration_unit,
                revoke_on_refund: product.revoke_on_refund,
                requires_license_key: product.requires_license_key,
                watermark_pdfs: product.watermark_pdfs,
            });
            setFiles(product.files || []);
        }).catch(() => setError('Could not load this digital product.'));
    }, [id, isEditing]);

    const field = (key) => (value) => setForm((f) => ({ ...f, [key]: value }));

    const pickProduct = useCallback(async () => {
        if (!window.shopify?.resourcePicker) {
            setError('The product picker is only available inside Shopify admin.');
            return;
        }

        const selected = await window.shopify.resourcePicker({ type: 'product', multiple: false });
        const product = selected?.[0];
        if (!product) return;

        const variants = product.variants || [];

        setForm((f) => ({
            ...f,
            shopify_product_id: numericId(product.id),
            shopify_product_title: product.title,
            shopify_variant_id: '',
            shopify_variant_title: '',
        }));
        setProductImage(product.images?.[0]?.originalSrc || product.images?.[0]?.url || null);
        setVariantOptions(
            variants.length > 1
                ? variants.map((v) => ({ label: v.title, value: numericId(v.id) }))
                : []
        );
    }, []);

    const selectVariant = useCallback((variantId) => {
        const variant = variantOptions.find((v) => v.value === variantId);
        setForm((f) => ({
            ...f,
            shopify_variant_id: variantId,
            shopify_variant_title: variant?.label || '',
        }));
    }, [variantOptions]);

    const buildPayload = useCallback(() => ({
        ...form,
        max_downloads: form.max_downloads === '' ? null : Number(form.max_downloads),
        expiration_value: form.expiration_value === '' ? null : Number(form.expiration_value),
        shopify_variant_id: form.shopify_variant_id || null,
        shopify_variant_title: form.shopify_variant_title || null,
    }), [form]);

    const handleSave = useCallback(async () => {
        setSaving(true);
        setError(null);
        try {
            if (isEditing) {
                await api.put(`/digital-products/${id}`, buildPayload());
            } else {
                const created = await api.post('/digital-products', buildPayload());
                navigate(`/digital-products/${created.id}`, { replace: true });
                return;
            }
            navigate('/digital-products');
        } catch (e) {
            setError(e.body?.message || 'Could not save this digital product.');
        } finally {
            setSaving(false);
        }
    }, [buildPayload, id, isEditing, navigate]);

    const handleDrop = useCallback(async (_dropped, accepted) => {
        if (!isEditing && !form.shopify_product_id) {
            setError('Select a Shopify product first.');
            return;
        }

        setUploading(true);
        setError(null);
        try {
            // Not saved yet — create it now rather than making the merchant
            // save first and come back to add files as a separate step.
            let productId = id;
            if (!isEditing) {
                const created = await api.post('/digital-products', buildPayload());
                productId = created.id;
                navigate(`/digital-products/${productId}`, { replace: true });
            }

            for (const file of accepted) {
                const uploaded = await uploadFile(productId, file);
                setFiles((prev) => [...prev, uploaded]);
            }
        } catch (e) {
            setError(e.body?.message || 'File upload failed. Please try again.');
        } finally {
            setUploading(false);
        }
    }, [id, isEditing, form.shopify_product_id, buildPayload, navigate]);

    const removeFile = async (fileId) => {
        await api.delete(`/files/${fileId}`);
        setFiles((prev) => prev.filter((f) => f.id !== fileId));
    };

    const replaceFile = async (fileId, newFile) => {
        setUploading(true);
        setError(null);
        try {
            // Upload the replacement first, then drop the old record — so a
            // failed upload never leaves the product with no file at all.
            const uploaded = await uploadFile(id, newFile);
            await api.delete(`/files/${fileId}`);
            setFiles((prev) => [...prev.filter((f) => f.id !== fileId), uploaded]);
        } catch (e) {
            setError(e.body?.message || 'Could not replace this file. Please try again.');
        } finally {
            setUploading(false);
        }
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
                    <BlockStack gap="300">
                        <Text as="h2" variant="headingMd">Shopify product</Text>

                        {form.shopify_product_title ? (
                            <InlineStack gap="300" blockAlign="center">
                                {productImage && <Thumbnail source={productImage} alt={form.shopify_product_title} size="small" />}
                                <BlockStack gap="050">
                                    <Text as="span" fontWeight="medium">{form.shopify_product_title}</Text>
                                    <Text as="span" tone="subdued">ID {form.shopify_product_id}</Text>
                                </BlockStack>
                                <Button onClick={pickProduct}>Change product</Button>
                            </InlineStack>
                        ) : (
                            <Button onClick={pickProduct}>Select a Shopify product</Button>
                        )}

                        {variantOptions.length > 0 && (
                            <Select
                                label="Variant"
                                options={[{ label: 'All variants (map the whole product)', value: '' }, ...variantOptions]}
                                value={form.shopify_variant_id}
                                onChange={selectVariant}
                            />
                        )}

                        <Select
                            label="Status"
                            options={[{ label: 'Active', value: 'active' }, { label: 'Inactive', value: 'inactive' }]}
                            value={form.status}
                            onChange={field('status')}
                        />
                    </BlockStack>
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
                        <Checkbox
                            label="Generate a license key for each purchase"
                            helpText="Shown to the customer alongside the download link — useful for software, plugins, or templates."
                            checked={form.requires_license_key}
                            onChange={field('requires_license_key')}
                        />
                        <Checkbox
                            label="Watermark PDF files with the customer's order info"
                            helpText="Only applies to files uploaded as PDF."
                            checked={form.watermark_pdfs}
                            onChange={field('watermark_pdfs')}
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
                                <InlineStack gap="200" blockAlign="center">
                                    <BlockStack gap="0">
                                        <Text as="span">{file.original_filename}</Text>
                                        <Text as="span" tone="subdued" variant="bodySm">
                                            {formatBytes(file.size_bytes)} · {file.mime_type}
                                        </Text>
                                    </BlockStack>
                                    <Badge tone={file.status === 'ready' ? 'success' : 'attention'}>
                                        {file.status === 'ready' ? 'Ready' : 'Pending'}
                                    </Badge>
                                </InlineStack>
                                <InlineStack gap="200">
                                    <Button
                                        variant="plain"
                                        disabled={uploading}
                                        onClick={() => document.getElementById(`replace-${file.id}`).click()}
                                    >
                                        Replace
                                    </Button>
                                    <input
                                        id={`replace-${file.id}`}
                                        type="file"
                                        hidden
                                        onChange={(e) => {
                                            const picked = e.target.files[0];
                                            e.target.value = '';
                                            if (picked) replaceFile(file.id, picked);
                                        }}
                                    />
                                    <Button variant="plain" tone="critical" onClick={() => removeFile(file.id)}>
                                        Remove
                                    </Button>
                                </InlineStack>
                            </InlineStack>
                        ))}
                    </BlockStack>
                </Card>
            </BlockStack>
        </Page>
    );
}

function formatBytes(bytes) {
    if (!bytes) return '0 KB';
    const mb = bytes / (1024 * 1024);
    if (mb >= 1) return `${mb.toFixed(1)} MB`;
    return `${(bytes / 1024).toFixed(0)} KB`;
}
