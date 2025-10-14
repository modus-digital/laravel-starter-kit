<?php

declare(strict_types=1);

return [
    'image_upload' => [
        'cta' => [
            'click' => 'Click to upload',
            'or_drag' => 'or drag and drop',
        ],
        'formats' => 'PNG, JPG, GIF, WEBP up to :size',
        'status' => [
            'uploading' => 'Uploading…',
        ],
        'errors' => [
            'invalid_type' => 'Please upload a valid image file',
            'size_exceeded' => 'File size must be less than :size',
            'preview_failed' => 'Image preview failed to load',
        ],
    ],
];
