import { api } from './api';

/**
 * Uploads a single browser File directly to the Railway Storage Bucket
 * using a presigned URL — the file bytes never pass through this app's
 * server. See app/Http/Controllers/Api/FileController.php for the
 * matching backend flow.
 */
export async function uploadFile(digitalProductId, file) {
    const { file: fileRecord, upload_url } = await api.post(`/digital-products/${digitalProductId}/files`, {
        original_filename: file.name,
        mime_type: file.type || 'application/octet-stream',
        size_bytes: file.size,
    });

    const putResponse = await fetch(upload_url, {
        method: 'PUT',
        headers: { 'Content-Type': file.type || 'application/octet-stream' },
        body: file,
    });

    if (!putResponse.ok) {
        throw new Error('Upload to storage failed.');
    }

    return api.patch(`/files/${fileRecord.id}/complete`);
}
