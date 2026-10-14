/**
 * Client-side feedback: toast store + confirm dialog.
 *
 *   CF.toast.success('Data tersimpan.')           // or Alpine.store('toast').success(...)
 *   CF.toast.push({ tone: 'warning', title: 'Perhatian', message: '…', timeout: 8000 })
 *   if (await CF.confirm({ message: 'Hapus kelas ini?' })) { … }
 *
 * Declarative confirm (no JS needed in views):
 *   <form data-confirm="Hapus kelas ini?" data-confirm-title="Hapus kelas"
 *         data-confirm-ok="Hapus" data-confirm-tone="danger"> … </form>
 *   <a href="…" data-confirm="Keluar dari kelas?">…</a>
 *   <button type="submit" data-confirm="Kirim sekarang?">…</button>
 *
 * Toasts are rendered by <x-ui.toast/> (aria-live region). Success/info/
 * warning auto-hide after 5 s; errors stay until dismissed.
 */

let sequence = 0;

const DEFAULT_TIMEOUT = 5000;

/**
 * Build the Alpine toast store.
 *
 * @returns {object} store with items, push(), success(), error(), warning(), info(), dismiss()
 */
export function createToastStore() {
    return {
        items: [],

        push({ tone = 'info', title = null, message = '', timeout } = {}) {
            const normalisedTone = tone === 'danger' ? 'error' : tone;
            const id = ++sequence;
            const ttl = timeout ?? (normalisedTone === 'error' ? 0 : DEFAULT_TIMEOUT);

            this.items.push({ id, tone: normalisedTone, title, message });

            if (ttl > 0) {
                window.setTimeout(() => this.dismiss(id), ttl);
            }

            return id;
        },

        success(message, title = null, options = {}) {
            return this.push({ ...options, tone: 'success', title, message });
        },

        error(message, title = null, options = {}) {
            return this.push({ ...options, tone: 'error', title, message });
        },

        warning(message, title = null, options = {}) {
            return this.push({ ...options, tone: 'warning', title, message });
        },

        info(message, title = null, options = {}) {
            return this.push({ ...options, tone: 'info', title, message });
        },

        dismiss(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },

        clear() {
            this.items = [];
        },
    };
}

/**
 * Accessible confirm dialog built on native <dialog> (focus trap, Esc, inert
 * background for free). Focus starts on "Batal" so Enter never destroys data.
 *
 * @param {object} options
 * @param {string} [options.title]
 * @param {string} [options.message]
 * @param {string} [options.confirmText]
 * @param {string} [options.cancelText]
 * @param {'danger'|'primary'} [options.tone]
 * @returns {Promise<boolean>} true when confirmed
 */
export function confirmDialog({
    title = 'Apakah Anda yakin?',
    message = '',
    confirmText = 'Ya, lanjutkan',
    cancelText = 'Batal',
    tone = 'danger',
} = {}) {
    if (typeof HTMLDialogElement === 'undefined') {
        return Promise.resolve(window.confirm([title, message].filter(Boolean).join('\n\n')));
    }

    return new Promise((resolve) => {
        const id = `cf-confirm-${++sequence}`;
        const dialog = document.createElement('dialog');
        dialog.className = 'cf-dialog';
        dialog.setAttribute('aria-labelledby', `${id}-title`);
        dialog.setAttribute('aria-describedby', `${id}-message`);

        dialog.innerHTML = `
            <form method="dialog">
                <div class="px-6 pb-2 pt-6">
                    <h2 class="modal-title" id="${id}-title"></h2>
                    <p class="modal-description" id="${id}-message"></p>
                </div>
                <div class="modal-footer mt-4">
                    <button type="submit" value="cancel" class="btn btn-secondary" data-role="cancel"></button>
                    <button type="submit" value="confirm" class="btn ${tone === 'danger' ? 'btn-danger' : 'btn-primary'}" data-role="confirm"></button>
                </div>
            </form>`;

        // Reason: set user-provided strings via textContent, never innerHTML.
        dialog.querySelector(`#${id}-title`).textContent = title;
        dialog.querySelector(`#${id}-message`).textContent = message;
        dialog.querySelector('[data-role="cancel"]').textContent = cancelText;
        dialog.querySelector('[data-role="confirm"]').textContent = confirmText;

        if (!message) {
            dialog.querySelector(`#${id}-message`).remove();
            dialog.removeAttribute('aria-describedby');
        }

        dialog.addEventListener('close', () => {
            resolve(dialog.returnValue === 'confirm');
            dialog.remove();
        });

        document.body.appendChild(dialog);
        dialog.showModal();
        dialog.querySelector('[data-role="cancel"]').focus();
    });
}

function optionsFrom(element) {
    return {
        title: element.dataset.confirmTitle || 'Apakah Anda yakin?',
        message: element.dataset.confirm || '',
        confirmText: element.dataset.confirmOk || 'Ya, lanjutkan',
        cancelText: element.dataset.confirmCancel || 'Batal',
        tone: element.dataset.confirmTone || 'danger',
    };
}

/** Wire [data-confirm] on forms, submit buttons and links (event delegation). */
export function installDeclarativeConfirm() {
    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            const source = event.submitter?.dataset.confirm !== undefined ? event.submitter : form;
            if (source.dataset.confirm === undefined || form.dataset.cfConfirmed === '1') {
                delete form.dataset.cfConfirmed;
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            const submitter = event.submitter;

            confirmDialog(optionsFrom(source)).then((ok) => {
                if (!ok) return;
                form.dataset.cfConfirmed = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
                } else {
                    form.submit();
                }
            });
        },
        true,
    );

    document.addEventListener(
        'click',
        (event) => {
            const link = event.target.closest?.('a[data-confirm]');
            if (!link || link.dataset.cfConfirmed === '1') {
                if (link) delete link.dataset.cfConfirmed;
                return;
            }

            event.preventDefault();
            confirmDialog(optionsFrom(link)).then((ok) => {
                if (!ok) return;
                link.dataset.cfConfirmed = '1';
                link.click();
            });
        },
        true,
    );
}
