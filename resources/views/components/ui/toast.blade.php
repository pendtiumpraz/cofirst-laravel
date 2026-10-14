{{--
    Toast region — include ONCE per layout, right before </body>:  <x-ui.toast />

    Renders session flashes (success | error | warning | info | message | status) and a validation
    summary when $errors is not empty, plus client toasts from Alpine.store('toast') / CF.toast.
    Success/info/warning auto-hide after 5 s; errors stay until closed. aria-live region.

    Controllers: return back()->with('success', 'Kelas berhasil disimpan.');
                 ->with('error', ['title' => 'Gagal menyimpan', 'message' => 'Coba lagi.'])  (array form)
    JS:          CF.toast.success('Tersimpan.') · Alpine.store('toast').error('Gagal.', 'Judul')
    :validation="false" hides the validation summary (e.g. on pages with an inline error summary).
--}}
@props([
    'validation' => true,
])

@php
    $statusMessages = [
        'profile-updated' => 'Profil berhasil diperbarui.',
        'password-updated' => 'Kata sandi berhasil diperbarui.',
        'verification-link-sent' => 'Tautan verifikasi baru telah dikirim ke email Anda.',
        'photo-updated' => 'Foto profil berhasil diperbarui.',
        'photo-deleted' => 'Foto profil dihapus.',
    ];

    $titles = [
        'success' => 'Berhasil',
        'error' => 'Terjadi kesalahan',
        'warning' => 'Perhatian',
        'info' => 'Informasi',
    ];

    $flashes = [];
    $push = function ($tone, $payload) use (&$flashes) {
        if (is_array($payload)) {
            $flashes[] = ['tone' => $tone, 'title' => $payload['title'] ?? null, 'message' => $payload['message'] ?? ''];
        } elseif (filled($payload) && is_scalar($payload)) {
            $flashes[] = ['tone' => $tone, 'title' => null, 'message' => (string) $payload];
        }
    };

    $session = session();
    foreach (['success', 'error', 'warning', 'info'] as $tone) {
        if ($session->has($tone)) {
            $push($tone, $session->get($tone));
        }
    }
    if ($session->has('message')) {
        $push('info', $session->get('message'));
    }
    if ($session->has('status')) {
        $status = $session->get('status');
        if (is_string($status)) {
            $text = $statusMessages[$status] ?? (str_contains($status, ' ') ? $status : null);
            if ($text) {
                $push('success', $text);
            }
        }
    }

    $bag = $errors ?? null;
    if ($validation && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->any()) {
        $count = count($bag->all());
        $flashes[] = [
            'tone' => 'error',
            'title' => 'Periksa kembali isian Anda',
            'message' => $count === 1 ? $bag->first() : "Ada {$count} isian yang perlu diperbaiki.",
        ];
    }

    $toneClass = fn ($tone) => match ($tone) {
        'success' => 'toast-success',
        'error' => 'toast-error',
        'warning' => 'toast-warning',
        default => 'toast-info',
    };
    $toneIcon = fn ($tone) => match ($tone) {
        'success' => 'check-circle',
        'error' => 'exclamation-circle',
        'warning' => 'exclamation-triangle',
        default => 'information-circle',
    };
@endphp

<div x-data class="toast-region" aria-live="polite" aria-relevant="additions" aria-label="Notifikasi" role="region" {{ $attributes }}>
    @foreach ($flashes as $flash)
        <div x-data="cfToastItem({{ $flash['tone'] === 'error' ? 0 : 5000 }})" x-show="show"
            x-transition:leave="transition-opacity duration-fast" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            role="{{ $flash['tone'] === 'error' ? 'alert' : 'status' }}"
            class="toast toast-enter {{ $toneClass($flash['tone']) }}">
            <x-ui.icon :name="$toneIcon($flash['tone'])" class="toast-icon" />
            <div class="min-w-0 flex-1">
                <p class="toast-title">{{ $flash['title'] ?? $titles[$flash['tone']] }}</p>
                @if (filled($flash['message']))
                    <p class="toast-body">{{ $flash['message'] }}</p>
                @endif
            </div>
            <button type="button" class="btn btn-ghost btn-icon btn-xs -mr-1 -mt-1" x-on:click="close()" aria-label="Tutup notifikasi">
                <x-ui.icon name="x-mark" />
            </button>
        </div>
    @endforeach

    <template x-for="toast in $store.toast.items" x-bind:key="toast.id">
        <div class="toast toast-enter" x-bind:class="'toast-' + toast.tone" x-bind:role="toast.tone === 'error' ? 'alert' : 'status'">
            <template x-if="toast.tone === 'success'"><x-ui.icon name="check-circle" class="toast-icon" /></template>
            <template x-if="toast.tone === 'error'"><x-ui.icon name="exclamation-circle" class="toast-icon" /></template>
            <template x-if="toast.tone === 'warning'"><x-ui.icon name="exclamation-triangle" class="toast-icon" /></template>
            <template x-if="toast.tone === 'info'"><x-ui.icon name="information-circle" class="toast-icon" /></template>
            <div class="min-w-0 flex-1">
                <p class="toast-title" x-text="toast.title || @js($titles)[toast.tone]"></p>
                <p class="toast-body" x-show="toast.message" x-text="toast.message"></p>
            </div>
            <button type="button" class="btn btn-ghost btn-icon btn-xs -mr-1 -mt-1" x-on:click="$store.toast.dismiss(toast.id)" aria-label="Tutup notifikasi">
                <x-ui.icon name="x-mark" />
            </button>
        </div>
    </template>
</div>
