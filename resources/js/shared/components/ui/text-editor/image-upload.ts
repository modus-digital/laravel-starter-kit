import { createImageUpload } from 'novel';
import { toast } from 'sonner';

export const onUpload = async (file: File): Promise<string> => {
    const formData = new FormData();
    formData.append('image', file);

    const toastId = toast.loading('Uploading image...');

    try {
        const response = await fetch('/api/v1/upload/image', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
            credentials: 'same-origin',
        });

        if (response.status === 200) {
            const { url } = (await response.json()) as { url: string };

            // Preload the image
            await new Promise<void>((resolve, reject) => {
                const image = new Image();
                image.src = url;
                image.onload = () => resolve();
                image.onerror = () => reject(new Error('Failed to load uploaded image'));
            });

            toast.success('Image uploaded successfully.', { id: toastId });
            return url;
        } else if (response.status === 401) {
            toast.error('You must be logged in to upload images.', { id: toastId });
            throw new Error('You must be logged in to upload images.');
        } else if (response.status === 422) {
            const errorData = await response.json();
            const errorMessage = errorData.message || 'Validation error. Please check the image file.';
            toast.error(errorMessage, { id: toastId });
            throw new Error(errorMessage);
        } else {
            toast.error('Error uploading image. Please try again.', { id: toastId });
            throw new Error('Error uploading image. Please try again.');
        }
    } catch (error) {
        toast.error(error instanceof Error ? error.message : 'Upload failed', { id: toastId });
        throw error;
    }
};

export const uploadFn = createImageUpload({
    onUpload,
    validateFn: (file) => {
        if (!file.type.includes('image/')) {
            toast.error('File type not supported.');
            return false;
        }
        if (file.size / 1024 / 1024 > 20) {
            toast.error('File size too big (max 20MB).');
            return false;
        }
        return true;
    },
});
