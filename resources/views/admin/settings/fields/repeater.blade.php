{{--
    repeater — rows of sub-fields (FAQ, stats, bank accounts) with add, remove and
    reorder. Server-side errors are attached to their row so they survive reordering.
--}}
@php
    $subs = $field['fields'];
    $rows = old($field['path'], is_array($value) ? $value : []);
    $rows = array_values(array_filter(is_array($rows) ? $rows : [], 'is_array'));
    $maxRows = (int) ($field['max'] ?? 20);

    $rowErrors = [];
    foreach ($rows as $i => $row) {
        foreach (array_keys($subs) as $name) {
            if ($message = $errors->first($field['path'].'.'.$i.'.'.$name)) {
                $rowErrors[$i][$name] = $message;
            }
        }
    }

    $blank = array_fill_keys(array_keys($subs), '');
    $textCols = collect($subs)->reject(fn ($sub) => $sub['type'] === 'textarea')->count();
    $gridCols = match (true) {
        $textCols >= 3 => 'sm:grid-cols-3',
        $textCols === 2 => 'sm:grid-cols-2',
        default => '',
    };
    $groupMessage = $errors->first($field['path']);
@endphp
<fieldset class="field min-w-0"
    x-data="{
        rows: @js($rows).map((row, i) => ({ ...@js($blank), ...row, _id: i + 1, _errors: (@js((object) $rowErrors))[i] || {} })),
        next: {{ count($rows) + 1 }},
        max: {{ $maxRows }},
        base: @js($field['name']),
        name(i, key) { return `${this.base}[${i}][${key}]` },
        id(i, key) { return `{{ $field['id'] }}_${i}_${key}` },
        add() {
            if (this.rows.length >= this.max) return;
            this.rows.push({ ...@js($blank), _id: this.next++, _errors: {} });
            this.$dispatch('input');
            this.$nextTick(() => this.$root.querySelector(`#${this.id(this.rows.length - 1, @js(array_key_first($subs)))}`)?.focus());
        },
        remove(i) { this.rows.splice(i, 1); this.$dispatch('input') },
        move(i, step) {
            const j = i + step;
            if (j < 0 || j >= this.rows.length) return;
            [this.rows[i], this.rows[j]] = [this.rows[j], this.rows[i]];
            this.$dispatch('input');
        },
    }">
    <legend class="label mb-1">{{ $field['label'] }}</legend>
    @if ($field['help'])
        <p class="hint -mt-0.5 mb-1">{{ $field['help'] }}</p>
    @endif

    <ol role="list" class="space-y-3">
        <template x-for="(row, i) in rows" :key="row._id">
            <li class="rounded-lg border border-line bg-surface-2/60 p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <span class="eyebrow" x-text="'Baris ' + (i + 1)"></span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="btn btn-ghost btn-icon btn-xs" x-on:click="move(i, -1)" x-bind:disabled="i === 0"
                            x-bind:aria-label="'Pindahkan baris ' + (i + 1) + ' ke atas'">
                            <x-ui.icon name="arrow-up" />
                        </button>
                        <button type="button" class="btn btn-ghost btn-icon btn-xs" x-on:click="move(i, 1)" x-bind:disabled="i === rows.length - 1"
                            x-bind:aria-label="'Pindahkan baris ' + (i + 1) + ' ke bawah'">
                            <x-ui.icon name="arrow-down" />
                        </button>
                        <button type="button" class="btn btn-danger-ghost btn-icon btn-xs" x-on:click="remove(i)"
                            x-bind:aria-label="'Hapus baris ' + (i + 1)">
                            <x-ui.icon name="trash" />
                        </button>
                    </div>
                </div>

                <div class="grid gap-3 {{ $gridCols }}">
                    @foreach ($subs as $name => $sub)
                        <div @class(['field min-w-0', 'sm:col-span-full' => $sub['type'] === 'textarea'])>
                            <label class="label" x-bind:for="id(i, '{{ $name }}')">{{ $sub['label'] }}</label>
                            @if ($sub['type'] === 'textarea')
                                <textarea class="input" rows="3" x-bind:id="id(i, '{{ $name }}')" x-bind:name="name(i, '{{ $name }}')"
                                    x-model="row['{{ $name }}']" x-bind:aria-invalid="row._errors['{{ $name }}'] ? 'true' : null"></textarea>
                            @else
                                <input class="input" type="{{ in_array($sub['type'], ['url', 'number'], true) ? $sub['type'] : 'text' }}"
                                    x-bind:id="id(i, '{{ $name }}')" x-bind:name="name(i, '{{ $name }}')"
                                    x-model="row['{{ $name }}']" x-bind:aria-invalid="row._errors['{{ $name }}'] ? 'true' : null">
                            @endif
                            <p class="error-text" x-show="row._errors['{{ $name }}']" x-cloak>
                                <x-ui.icon name="m-exclamation-circle" />
                                <span x-text="row._errors['{{ $name }}']"></span>
                            </p>
                        </div>
                    @endforeach
                </div>
            </li>
        </template>
    </ol>

    <p x-show="rows.length === 0" @if ($rows !== []) x-cloak @endif
        class="rounded-lg border border-dashed border-line px-4 py-6 text-center text-sm text-fg-muted">
        Belum ada isi. Gunakan “Tambah baris” untuk menambahkan.
    </p>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-ui.button variant="secondary" size="sm" icon="plus" x-on:click="add()" x-bind:disabled="rows.length >= max">
            Tambah baris
        </x-ui.button>
        <span class="num text-xs text-fg-subtle"><span x-text="rows.length">{{ count($rows) }}</span> dari maks. {{ $maxRows }}</span>
    </div>

    @if ($groupMessage)
        <p class="error-text"><x-ui.icon name="m-exclamation-circle" /><span>{{ $groupMessage }}</span></p>
    @endif
</fieldset>
