@props([
    'name' => 'photo',
    'currentPhoto' => null,
    'accept' => 'image/*',
    'maxSize' => 5120, // 5MB default
    'enableCrop' => false,
    'aspectRatio' => 1
])

<div 
    x-data="photoUpload()"
    x-init="init()"
    {{ $attributes->merge(['class' => 'photo-upload-container']) }}
>
    <div class="space-y-4">
        <!-- Current Photo Preview -->
        <!-- Debug: currentPhoto = {{ $currentPhoto ?? 'null' }} -->
        @if($currentPhoto)
        <div class="current-photo">
            <img src="{{ $currentPhoto }}" alt="Current photo" class="w-32 h-32 object-cover rounded-lg">
        </div>
        @else
        <!-- No current photo -->
        @endif

        <!-- Upload Area -->
        <div 
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            :class="{'border-indigo-500 bg-indigo-50': isDragging}"
            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors"
        >
            <input 
                type="file" 
                :name="name"
                @change="handleFileSelect"
                accept="{{ $accept }}"
                class="hidden"
                x-ref="fileInput"
            >

            <div x-show="!preview" class="space-y-2">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="text-sm text-gray-600">
                    <button 
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="font-medium text-indigo-600 hover:text-indigo-500"
                    >
                        Click to upload
                    </button>
                    or drag and drop
                </div>
                <p class="text-xs text-gray-500">
                    PNG, JPG, GIF up to {{ $maxSize / 1024 }}MB
                </p>
            </div>

            <!-- Preview -->
            <div x-show="preview" class="relative">
                <img :src="preview" class="max-h-64 mx-auto rounded-lg">
                <button 
                    type="button"
                    @click="removeFile"
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Crop Data (Hidden) -->
        @if($enableCrop)
        <input type="hidden" name="crop_data" x-model="cropDataJson">
        @endif

        <!-- Error Message -->
        <div x-show="error" x-text="error" class="text-sm text-red-600"></div>
    </div>
</div>

<script>
function photoUpload() {
    return {
        name: '{{ $name }}',
        maxSize: {{ $maxSize }} * 1024, // Convert to bytes
        enableCrop: {{ $enableCrop ? 'true' : 'false' }},
        aspectRatio: {{ $aspectRatio }},
        isDragging: false,
        preview: null,
        file: null,
        error: null,
        cropData: null,
        cropDataJson: '',

        init() {
            // Initialize cropper if needed
            if (this.enableCrop) {
                // Load Cropper.js if not already loaded
                if (typeof Cropper === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js';
                    document.head.appendChild(script);

                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css';
                    document.head.appendChild(link);
                }
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        },

        processFile(file) {
            this.error = null;

            // Validate file type
            if (!file.type.match('image.*')) {
                this.error = 'Please select an image file.';
                return;
            }

            // Validate file size
            if (file.size > this.maxSize) {
                this.error = `File size must be less than ${this.maxSize / 1024 / 1024}MB.`;
                return;
            }

            this.file = file;

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.preview = e.target.result;

                if (this.enableCrop) {
                    this.$nextTick(() => {
                        this.initCropper();
                    });
                }
            };
            reader.readAsDataURL(file);
        },

        initCropper() {
            // Implementation for cropper initialization
            // This would open a modal with Cropper.js
            console.log('Cropper initialization would happen here');
        },

        removeFile() {
            this.preview = null;
            this.file = null;
            this.error = null;
            this.cropData = null;
            this.cropDataJson = '';
            this.$refs.fileInput.value = '';
        }
    }
}
</script>