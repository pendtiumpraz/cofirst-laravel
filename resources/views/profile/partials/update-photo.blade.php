<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Photo') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your profile photo.') }}
        </p>
    </header>

    <form 
        method="post" 
        action="{{ route('profile.photo.upload') }}" 
        enctype="multipart/form-data"
        class="mt-6 space-y-6"
        x-data="profilePhotoForm()"
        @submit.prevent="submitForm"
    >
        @csrf

        <div>
            <x-photo-upload 
                name="photo"
                :current-photo="$user->profile_photo_url"
                :enable-crop="true"
                :aspect-ratio="1"
                :max-size="5120"
            />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button type="submit" :disabled="isSubmitting">
                <span x-show="!isSubmitting">{{ __('Save') }}</span>
                <span x-show="isSubmitting">{{ __('Uploading...') }}</span>
            </x-primary-button>

            @if($user->profile_photo_path)
            <x-danger-button 
                type="button"
                @click="deletePhoto"
                :disabled="isDeleting"
            >
                <span x-show="!isDeleting">{{ __('Remove Photo') }}</span>
                <span x-show="isDeleting">{{ __('Removing...') }}</span>
            </x-danger-button>
            @endif

            <p x-show="message" x-text="message" :class="messageClass" class="text-sm"></p>
        </div>
    </form>
</section>

<script>
function profilePhotoForm() {
    return {
        isSubmitting: false,
        isDeleting: false,
        message: '',
        messageClass: '',

        submitForm(e) {
            this.isSubmitting = true;
            this.message = '';

            const formData = new FormData(e.target);

            fetch(e.target.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.message = data.message;
                    this.messageClass = 'text-green-600';
                    // Reload page to show new photo
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.message = data.message || 'An error occurred';
                    this.messageClass = 'text-red-600';
                }
            })
            .catch(error => {
                this.message = 'An error occurred while uploading';
                this.messageClass = 'text-red-600';
            })
            .finally(() => {
                this.isSubmitting = false;
            });
        },

        deletePhoto() {
            if (!confirm('Are you sure you want to remove your profile photo?')) {
                return;
            }

            this.isDeleting = true;
            this.message = '';

            fetch('{{ route("profile.photo.delete") }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.message = data.message;
                    this.messageClass = 'text-green-600';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.message = data.message || 'An error occurred';
                    this.messageClass = 'text-red-600';
                }
            })
            .catch(error => {
                this.message = 'An error occurred while deleting';
                this.messageClass = 'text-red-600';
            })
            .finally(() => {
                this.isDeleting = false;
            });
        }
    }
}
</script>