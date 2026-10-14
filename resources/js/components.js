/**
 * Alpine.data() behaviours used by the <x-ui.*> Blade components.
 * Registered once in app.js (never redefined per component instance).
 */

/** Name carried by open-modal / close-modal events: 'name' or { name }. */
function eventName(detail) {
    if (typeof detail === 'string') return detail;
    return detail?.name ?? null;
}

export default function registerComponents(Alpine) {
    /**
     * <x-ui.modal>: open/close via window events
     *   $dispatch('open-modal', 'kelas-baru')  /  $dispatch('close-modal', 'kelas-baru')
     * Focus is trapped with @alpinejs/focus (x-trap) and restored on close.
     */
    Alpine.data('cfModal', (name, initiallyOpen = false) => ({
        name,
        open: initiallyOpen,

        init() {
            this.$watch('open', (value) => {
                this.$dispatch(value ? 'modal-opened' : 'modal-closed', this.name);
            });
        },

        onOpen(event) {
            if (eventName(event.detail) === this.name) this.open = true;
        },

        onClose(event) {
            if (eventName(event.detail) === this.name) this.open = false;
        },

        show() {
            this.open = true;
        },

        hide() {
            this.open = false;
        },
    }));

    /** <x-ui.dropdown>: disclosure menu with arrow-key navigation. */
    Alpine.data('cfDropdown', () => ({
        open: false,

        toggle() {
            this.open ? this.close() : this.show();
        },

        show() {
            this.open = true;
            this.$nextTick(() => this.items()[0]?.focus({ preventScroll: true }));
        },

        close(returnFocus = false) {
            if (!this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.trigger?.querySelector('button, a, [tabindex]')?.focus();
        },

        items() {
            return [...(this.$refs.panel?.querySelectorAll('a[href], button:not([disabled]), [role="menuitem"]') ?? [])];
        },

        move(step) {
            const items = this.items();
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            const next = index === -1 ? 0 : (index + step + items.length) % items.length;
            items[next].focus();
        },
    }));

    /**
     * <x-ui.tabs>: roving tabindex, arrow keys, optional URL hash (#key).
     */
    Alpine.data('cfTabs', (keys = [], initial = null, useHash = true) => ({
        keys,
        active: initial ?? keys[0] ?? null,

        init() {
            if (useHash) {
                this.fromHash();
                window.addEventListener('hashchange', () => this.fromHash());
            }
        },

        fromHash() {
            const hash = decodeURIComponent(window.location.hash.replace(/^#/, ''));
            if (this.keys.includes(hash)) this.active = hash;
        },

        select(key, focus = false) {
            if (!this.keys.includes(key)) return;
            this.active = key;

            if (useHash && window.history?.replaceState) {
                // Reason: replaceState updates the hash without scrolling the page.
                window.history.replaceState(null, '', `#${encodeURIComponent(key)}`);
            }

            if (focus) {
                this.$nextTick(() => this.$root.querySelector(`[data-tab-key="${CSS.escape(key)}"]`)?.focus());
            }
        },

        onKeydown(event) {
            const index = this.keys.indexOf(this.active);
            const map = {
                ArrowRight: index + 1,
                ArrowLeft: index - 1,
                Home: 0,
                End: this.keys.length - 1,
            };

            if (!(event.key in map)) return;
            event.preventDefault();
            const next = (map[event.key] + this.keys.length) % this.keys.length;
            this.select(this.keys[next], true);
        },

        isActive(key) {
            return this.active === key;
        },
    }));

    /**
     * <x-ui.file>: dropzone that really attaches dropped files to the input,
     * validates size/type client-side and previews images.
     */
    Alpine.data('cfFileDrop', ({ maxKb = 0, accept = '', multiple = false } = {}) => ({
        dragging: false,
        files: [],
        preview: null,
        error: null,

        onDrop(event) {
            this.dragging = false;
            const dropped = event.dataTransfer?.files;
            if (!dropped?.length) return;

            const transfer = new DataTransfer();
            [...dropped].slice(0, multiple ? dropped.length : 1).forEach((file) => transfer.items.add(file));
            this.$refs.input.files = transfer.files;
            this.onChange();
        },

        onChange() {
            this.error = null;
            const list = [...(this.$refs.input.files ?? [])];

            const tooBig = maxKb > 0 ? list.find((file) => file.size > maxKb * 1024) : null;
            if (tooBig) {
                this.error = `Ukuran berkas "${tooBig.name}" melebihi ${formatKb(maxKb)}.`;
                this.clear();
                return;
            }

            if (accept && !list.every((file) => matchesAccept(file, accept))) {
                this.error = 'Jenis berkas tidak didukung.';
                this.clear();
                return;
            }

            this.files = list.map((file) => ({ name: file.name, size: formatBytes(file.size) }));

            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = list[0]?.type?.startsWith('image/') ? URL.createObjectURL(list[0]) : null;
        },

        clear() {
            this.$refs.input.value = '';
            this.files = [];
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null;
        },
    }));

    /**
     * Legacy <x-photo-upload>: same props as before, now registered once.
     * Drag & drop attaches the file to the hidden input (bug fix).
     */
    Alpine.data('photoUpload', ({ maxSize = 5120, enableCrop = false, aspectRatio = 1 } = {}) => ({
        maxBytes: maxSize * 1024,
        enableCrop,
        aspectRatio,
        isDragging: false,
        preview: null,
        file: null,
        error: null,
        cropData: null,
        cropDataJson: '',

        init() {},

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer?.files;
            if (!files?.length) return;

            const transfer = new DataTransfer();
            transfer.items.add(files[0]);
            this.$refs.fileInput.files = transfer.files;
            this.processFile(files[0]);
        },

        handleFileSelect(event) {
            const files = event.target.files;
            if (files?.length) this.processFile(files[0]);
        },

        processFile(file) {
            this.error = null;

            if (!file.type.startsWith('image/')) {
                this.error = 'Pilih berkas gambar (JPG, PNG, atau WebP).';
                this.removeFile();
                return;
            }

            if (file.size > this.maxBytes) {
                this.error = `Ukuran berkas maksimal ${formatKb(maxSize)}.`;
                this.removeFile();
                return;
            }

            this.file = file;
            const reader = new FileReader();
            reader.onload = (event) => {
                this.preview = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        removeFile() {
            this.preview = null;
            this.file = null;
            this.cropData = null;
            this.cropDataJson = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
    }));

    /** One server-rendered toast (flash message) with auto-dismiss. */
    Alpine.data('cfToastItem', (timeout = 5000) => ({
        show: true,

        init() {
            if (timeout > 0) window.setTimeout(() => (this.show = false), timeout);
        },

        close() {
            this.show = false;
        },
    }));
}

function matchesAccept(file, accept) {
    const rules = accept
        .split(',')
        .map((rule) => rule.trim().toLowerCase())
        .filter(Boolean);

    if (!rules.length) return true;

    const name = file.name.toLowerCase();
    const type = (file.type || '').toLowerCase();

    return rules.some((rule) => {
        if (rule.startsWith('.')) return name.endsWith(rule);
        if (rule.endsWith('/*')) return type.startsWith(rule.slice(0, -1));
        return type === rule;
    });
}

function formatBytes(bytes) {
    if (bytes >= 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(1).replace('.', ',')} MB`;
    if (bytes >= 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${bytes} B`;
}

function formatKb(kb) {
    return kb >= 1024 ? `${(kb / 1024).toLocaleString('id-ID', { maximumFractionDigits: 1 })} MB` : `${kb} KB`;
}
