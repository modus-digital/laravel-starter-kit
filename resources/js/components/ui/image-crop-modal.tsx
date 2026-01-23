'use client';

import * as React from 'react';
import ReactCrop, { type Crop, type PixelCrop } from 'react-image-crop';
import 'react-image-crop/dist/ReactCrop.css';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { cn } from '@/lib/utils';

interface ImageCropModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    imageSrc: string | null;
    onCropComplete: (croppedImageBlob: Blob, aspectRatio: '1:1' | '16:9') => void;
    initialAspectRatio?: '1:1' | '16:9';
}

export function ImageCropModal({
    open,
    onOpenChange,
    imageSrc,
    onCropComplete,
    initialAspectRatio = '1:1',
}: ImageCropModalProps) {
    const [aspectRatio, setAspectRatio] = React.useState<'1:1' | '16:9'>(initialAspectRatio);
    const [crop, setCrop] = React.useState<Crop>({ unit: '%', width: 100, height: 100, x: 0, y: 0 });
    const [completedCrop, setCompletedCrop] = React.useState<PixelCrop>();
    const imgRef = React.useRef<HTMLImageElement>(null);

    // Only lock aspect ratio for square mode
    const aspect = aspectRatio === '1:1' ? 1 : undefined;

    const getInitialCrop = (mediaWidth: number, mediaHeight: number): Crop => {
        if (aspectRatio === '1:1') {
            // For square, create a centered square crop
            const size = Math.min(mediaWidth, mediaHeight);
            const widthPercent = (size / mediaWidth) * 100;
            const heightPercent = (size / mediaHeight) * 100;
            return {
                unit: '%',
                width: widthPercent,
                height: heightPercent,
                x: (100 - widthPercent) / 2,
                y: (100 - heightPercent) / 2,
            };
        }
        // For wide, select full image
        return { unit: '%', width: 100, height: 100, x: 0, y: 0 };
    };

    React.useEffect(() => {
        if (imgRef.current) {
            const { naturalWidth, naturalHeight } = imgRef.current;
            setCrop(getInitialCrop(naturalWidth, naturalHeight));
        }
    }, [aspectRatio]);

    const onImageLoad = (e: React.SyntheticEvent<HTMLImageElement>) => {
        const { naturalWidth, naturalHeight } = e.currentTarget;
        setCrop(getInitialCrop(naturalWidth, naturalHeight));
    };

    const getCroppedImg = (image: HTMLImageElement, crop: PixelCrop): Promise<Blob> => {
        const canvas = document.createElement('canvas');
        const scaleX = image.naturalWidth / image.width;
        const scaleY = image.naturalHeight / image.height;
        const ctx = canvas.getContext('2d');

        if (!ctx) {
            throw new Error('No 2d context');
        }

        const pixelRatio = window.devicePixelRatio;
        canvas.width = crop.width * scaleX * pixelRatio;
        canvas.height = crop.height * scaleY * pixelRatio;

        ctx.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
        ctx.imageSmoothingQuality = 'high';

        const cropX = crop.x * scaleX;
        const cropY = crop.y * scaleY;

        ctx.drawImage(image, cropX, cropY, crop.width * scaleX, crop.height * scaleY, 0, 0, crop.width * scaleX, crop.height * scaleY);

        return new Promise((resolve, reject) => {
            canvas.toBlob(
                (blob) => {
                    if (!blob) {
                        reject(new Error('Canvas is empty'));
                        return;
                    }
                    resolve(blob);
                },
                'image/png',
                1,
            );
        });
    };

    const handleApply = async () => {
        if (imgRef.current && completedCrop) {
            try {
                const croppedBlob = await getCroppedImg(imgRef.current, completedCrop);
                onCropComplete(croppedBlob, aspectRatio);
                onOpenChange(false);
            } catch (error) {
                console.error('Error cropping image:', error);
            }
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Crop Logo</DialogTitle>
                    <DialogDescription>Select aspect ratio and crop your logo image</DialogDescription>
                </DialogHeader>

                <div className="space-y-4">
                    <div className="space-y-2">
                        <Label>Logo Display Mode</Label>
                        <RadioGroup value={aspectRatio} onValueChange={(value) => setAspectRatio(value as '1:1' | '16:9')}>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="1:1" id="ratio-1-1" />
                                <Label htmlFor="ratio-1-1" className="cursor-pointer font-normal">
                                    Square - Show logo with app name
                                </Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="16:9" id="ratio-16-9" />
                                <Label htmlFor="ratio-16-9" className="cursor-pointer font-normal">
                                    Wide - Show logo only (hide app name)
                                </Label>
                            </div>
                        </RadioGroup>
                    </div>

                    {imageSrc && (
                        <div className="flex justify-center overflow-hidden rounded-md border bg-muted">
                            <ReactCrop
                                crop={crop}
                                onChange={(_, percentCrop) => setCrop(percentCrop)}
                                onComplete={(c) => setCompletedCrop(c)}
                                aspect={aspect}
                                className={cn('max-h-[60vh] max-w-full')}
                            >
                                <img
                                    ref={imgRef}
                                    alt="Crop me"
                                    src={imageSrc}
                                    style={{ maxHeight: '60vh', maxWidth: '100%' }}
                                    onLoad={onImageLoad}
                                />
                            </ReactCrop>
                        </div>
                    )}
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="button" onClick={handleApply} disabled={!completedCrop}>
                        Apply Crop
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
