@props([
    'id' => 'image-upload-' . uniqid(),
    'label' => __('components.image_upload.form.label'),
    'accept' => 'image/*',
    'maxSize' => '5MB',
    'preview' => null,
    'required' => false,
])

@php
    $hasWireModel = $attributes->whereStartsWith('wire:model')->isNotEmpty();
@endphp

<div
    x-data="imageUpload('{{ $id }}', {{ $hasWireModel ? 'true' : 'false' }}, {{ $preview ? 'true' : 'false' }})"
    x-on:livewire-upload-start.window="uploading = true; uploadError = false; error = ''"
    x-on:livewire-upload-finish.window="uploading = false"
    x-on:livewire-upload-error.window="uploading = false; uploadError = true; error = '{{ __('components.image_upload.errors.preview_failed') }}'"
    class="w-full"
>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <!-- Upload Area -->
        <div
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="handleDrop($event)"
            :class="{ 'border-primary-500 bg-primary-50 dark:bg-primary-900/20': dragOver, 'border-zinc-300 dark:border-zinc-600': !dragOver }"
            class="relative border-2 border-dashed rounded-xl transition-all duration-200 ease-in-out p-2"
        >
            <!-- Preview Area -->
            <div x-show="preview" class="relative group" style="display: none;">
                <!-- Loading skeleton while image loads -->
                <div x-show="!imageLoaded" class="w-full h-64 bg-zinc-200 dark:bg-zinc-700 rounded-lg animate-pulse flex items-center justify-center">
                    <svg class="w-12 h-12 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Image always renders but visibility controlled by imageLoaded -->
                <img
                    x-ref="previewImage"
                    :src="preview"
                    alt="Preview"
                    class="w-full h-64 object-cover rounded-lg transition-opacity duration-300"
                    :class="{ 'opacity-0 absolute invisible': !imageLoaded, 'opacity-100 relative visible': imageLoaded }"
                    x-on:load="handleImageLoad()"
                    x-on:error="handleImageError()"
                >

                <div class="absolute inset-0 hover:bg-black/40 group-hover:bg-opacity-40 transition-all duration-200 rounded-lg flex items-center justify-center" x-show="imageLoaded">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-3">
                        <button
                            type="button"
                            @click="triggerFileInput()"
                            class="px-4 py-2 bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors duration-200 flex items-center gap-2 shadow-lg"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Change
                        </button>
                        <button
                            type="button"
                            @click="removeImage()"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200 flex items-center gap-2 shadow-lg"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Remove
                        </button>
                    </div>
                </div>

                <!-- File Info -->
                <div x-show="fileName" class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary-100 dark:bg-primary-900/50 rounded-lg">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate" x-text="fileName"></p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="fileSize"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Prompt -->
            <div
                x-show="!preview"
                class="p-8 text-center"
            >
                <div class="mx-auto w-16 h-16 mb-4 flex items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>

                <div class="mb-2">
                    <button
                        type="button"
                        @click="triggerFileInput()"
                        class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors duration-200"
                    >
                        {{ __('components.image_upload.cta.click') }}
                    </button>
                    <span class="text-zinc-500 dark:text-zinc-400"> {{ __('components.image_upload.cta.or_drag') }}</span>
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('components.image_upload.formats', ['size' => $maxSize]) }}
                </p>
            </div>

            <!-- Loading State -->
            <div
                x-show="uploading"
                class="absolute inset-0 bg-white dark:bg-zinc-900 bg-opacity-90 dark:bg-opacity-90 rounded-xl flex items-center justify-center"
            >
                <div class="text-center">
                    <svg class="animate-spin h-10 w-10 text-primary-600 dark:text-primary-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 0 1 4 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('components.image_upload.status.uploading') }}</p>
                </div>
            </div>
        </div>

        <!-- Hidden File Input -->
        <input
            type="file"
            id="{{ $id }}"
            {!! $attributes->whereStartsWith('wire:model') !!}
            accept="{{ $accept }}"
            @change="handleFileSelect($event)"
            class="hidden"
            @if($required) required @endif
        >
    </div>

    <!-- Error Message -->
    <div x-show="error" class="mt-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-red-700 dark:text-red-400" x-text="error"></p>
        </div>
    </div>
</div>

<script>
function imageUpload(id, hasWireModel = false, hasInitialPreview = false) {
    return {
        preview: @js($preview),
        fileName: '',
        fileSize: '',
        dragOver: false,
        uploading: false,
        error: '',
        uploadError: false,
        imageLoaded: false,
        hasWireModel: hasWireModel,
        hasInitialPreview: hasInitialPreview,

        init() {
            this.registerImageWatcher();

            if (this.hasInitialPreview && this.preview) {
                this.$nextTick(() => {
                    const image = this.$refs.previewImage;
                    if (image && image.complete && image.naturalWidth > 0) {
                        this.handleImageLoad();
                    }
                });
            }
        },

        triggerFileInput() {
            document.getElementById(id).click();
        },

        registerImageWatcher() {
            if (!this.hasWireModel) {
                return;
            }

            this.$watch('preview', (value) => {
                if (!value) {
                    this.resetPreviewState();
                    return;
                }

                this.imageLoaded = false;
                this.$nextTick(() => {
                    const image = this.$refs.previewImage;
                    if (!image) {
                        return;
                    }

                    if (image.complete && image.naturalWidth > 0) {
                        this.handleImageLoad();
                    }
                });
            });
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.uploading = true;
                this.imageLoaded = false;
                this.processFile(file);
            }
        },

        clearError() {
            this.error = '';
            this.uploadError = false;
        },

        handleDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files[0];

            if (file) {
                this.uploading = true;

                // Set the file to the input
                const input = document.getElementById(id);
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;

                // Trigger change event for Livewire
                input.dispatchEvent(new Event('change', { bubbles: true }));

                this.processFile(file);
            }
        },

        processFile(file) {
            this.clearError();

            // Ensure loading state while we process
            this.uploading = true;
            this.imageLoaded = false;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                this.uploading = false;
                this.uploadError = true;
                this.error = '{{ __('components.image_upload.errors.invalid_type') }}';
                return;
            }

            // Validate file size (configured max)
            const maxSizeBytes = (parseInt('{{ (int) filter_var($maxSize, FILTER_SANITIZE_NUMBER_INT) }}', 10) || 5) * 1024 * 1024;
            if (file.size > maxSizeBytes) {
                this.uploading = false;
                this.uploadError = true;
                this.error = '{{ __('components.image_upload.errors.size_exceeded', ['size' => $maxSize]) }}';
                return;
            }

            this.fileName = file.name;
            this.fileSize = this.formatFileSize(file.size);

            // Create preview
            this.preview = '';
            this.previewLoading = true;
            this.previewHasLoaded = false;

            const reader = new FileReader();
            reader.onload = (event) => {
                this.preview = event.target.result;
                requestAnimationFrame(() => {
                    const image = this.$refs.previewImage;
                    if (!image) {
                        this.handleImageError();
                        return;
                    }

                    if (image.complete && image.naturalWidth > 0) {
                        this.handleImageLoad();
                    } else {
                        image.onload = () => this.handleImageLoad();
                        image.onerror = () => this.handleImageError();
                    }
                });
            };
            reader.onerror = () => this.handleImageError();
            reader.readAsDataURL(file);

            // Trigger change event for Livewire
            const input = document.getElementById(this.id);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        removeImage() {
            this.resetPreviewState();

            const input = document.getElementById(id);
            input.value = '';

            // Trigger change event for Livewire
            input.dispatchEvent(new Event('change', { bubbles: true }));

            if (this.hasWireModel) {
                this.preview = null;
            }
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        handleImageLoad() {
            this.imageLoaded = true;
            this.uploading = false;
        },

        handleImageError(showMessage = true) {
            this.imageLoaded = false;
            this.previewHasLoaded = false;
            this.previewLoading = false;
            this.uploading = false;
            this.uploadError = true;
            if (!this.hasWireModel) {
                this.preview = null;
            }
            if (showMessage) {
                this.error = '{{ __('components.image_upload.errors.preview_failed') }}';
            }
        },

        resetPreviewState() {
            this.preview = null;
            this.fileName = '';
            this.fileSize = '';
            this.clearError();
            this.imageLoaded = false;
            this.uploading = false;
            this.uploadError = false;
        }
    };
}
</script>
