{{--
    Async user picker (names only) backed by admin.users.search.

    @include('admin.users.partials.user-picker', [
        'name' => 'children[]',           // hidden input name ('student_id' for single)
        'id' => 'children-picker',
        'role' => 'student',              // student | parent | teacher
        'selected' => [['id' => 1, 'name' => 'Andi', 'hint' => 'an••••@x.id']],
        'multiple' => true,
        'placeholder' => 'Cari nama siswa…',
        'excludeClass' => null,           // hide students already seated in this class
        'enableWhen' => null,             // Alpine expression gating submission (e.g. "roles.includes('parent')")
    ])
--}}
@php
    $multiple = $multiple ?? true;
    $selected = array_values($selected ?? []);
    $searchUrl = route('admin.users.search', array_filter(['role' => $role ?? null, 'exclude_class' => $excludeClass ?? null]));
    $inputId = $id ?? 'picker-'.\Illuminate\Support\Str::random(6);
    $errorKey = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $hasError = $errors->has($errorKey) || $errors->has($errorKey.'.*');
@endphp

<div class="relative" x-data="{
        q: '', results: [], open: false, loading: false, failed: false, active: -1,
        multiple: @js($multiple),
        selected: @js($selected),
        async search() {
            const term = this.q.trim();
            if (term.length < 2) { this.results = []; this.open = false; return; }
            this.loading = true; this.failed = false;
            try {
                const url = new URL(@js($searchUrl), window.location.origin);
                url.searchParams.set('q', term);
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (! res.ok) throw new Error(res.status);
                const json = await res.json();
                this.results = (json.data || []).filter(r => ! this.selected.some(s => s.id === r.id));
                this.open = true; this.active = this.results.length ? 0 : -1;
            } catch (e) { this.failed = true; this.open = true; this.results = []; }
            finally { this.loading = false; }
        },
        add(item) {
            if (! item) return;
            this.selected = this.multiple ? [...this.selected.filter(s => s.id !== item.id), item] : [item];
            this.q = ''; this.results = []; this.open = false; this.active = -1;
            this.$nextTick(() => this.$refs.input.focus());
        },
        remove(id) { this.selected = this.selected.filter(s => s.id !== id); },
        move(step) { if (! this.results.length) return; this.active = (this.active + step + this.results.length) % this.results.length; },
    }" x-on:click.outside="open = false">

    <ul class="mb-2 flex flex-wrap gap-2" x-show="selected.length" x-cloak role="list" aria-label="Terpilih">
        <template x-for="item in selected" :key="item.id">
            <li class="inline-flex min-h-[36px] items-center gap-2 rounded-md border border-line bg-surface-2 py-1 pl-3 pr-1 text-sm">
                <span class="font-medium text-fg" x-text="item.name"></span>
                <span class="text-xs text-fg-subtle" x-text="item.hint" x-show="item.hint"></span>
                <button type="button" class="btn btn-ghost btn-icon btn-xs" x-on:click="remove(item.id)" x-bind:aria-label="'Hapus ' + item.name">
                    <x-ui.icon name="x-mark" />
                </button>
                <input type="hidden" name="{{ $name }}" x-bind:value="item.id" @if (! empty($enableWhen)) x-bind:disabled="!({{ $enableWhen }})" @endif>
            </li>
        </template>
    </ul>

    <div class="input-group" x-show="multiple || ! selected.length">
        <x-ui.icon name="magnifying-glass" class="input-icon" />
        <input type="search" id="{{ $inputId }}" x-ref="input" x-model="q" class="input" autocomplete="off"
            placeholder="{{ $placeholder ?? 'Ketik minimal 2 huruf…' }}"
            role="combobox" aria-autocomplete="list" aria-controls="{{ $inputId }}-list"
            x-bind:aria-expanded="open.toString()" @if ($hasError) aria-invalid="true" @endif
            x-on:input.debounce.300ms="search()"
            x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
            x-on:keydown.enter.prevent="add(results[active])" x-on:keydown.escape="open = false">
    </div>

    <div id="{{ $inputId }}-list" x-show="open" x-cloak role="listbox"
        class="absolute z-30 mt-1 max-h-72 w-full overflow-y-auto rounded-lg border border-line-strong bg-surface p-1 shadow-2">
        <p class="px-3 py-2 text-sm text-fg-subtle" x-show="loading">Mencari…</p>
        <p class="px-3 py-2 text-sm text-danger" x-show="failed && ! loading">Pencarian gagal. Coba lagi.</p>
        <p class="px-3 py-2 text-sm text-fg-subtle" x-show="! loading && ! failed && ! results.length">Tidak ada yang cocok.</p>
        <template x-for="(item, index) in results" :key="item.id">
            <button type="button" role="option" x-on:click="add(item)" x-bind:aria-selected="(index === active).toString()"
                class="flex min-h-[44px] w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-left text-sm hover:bg-surface-2"
                x-bind:class="index === active ? 'bg-surface-2' : ''">
                <span class="font-medium text-fg" x-text="item.name"></span>
                <span class="text-xs text-fg-subtle" x-text="item.hint"></span>
            </button>
        </template>
    </div>
</div>
