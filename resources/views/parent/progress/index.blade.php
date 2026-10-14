<x-app-layout title="Progres anak">
    <x-ui.page-header title="Progres anak"
        subtitle="Seberapa jauh anak Anda di setiap kelas. Progres bertambah setiap kali pengajar melaporkan pertemuan." />

    @forelse ($rows as $row)
        <x-ui.card as="section" class="mb-6" padding="none">
            <x-slot:header>
                <div class="flex min-w-0 items-center gap-3">
                    <x-ui.avatar :user="$row['child']" size="md" decorative />
                    <div class="min-w-0">
                        <h2 class="card-title">{{ $row['child']->name }}</h2>
                        <p class="card-subtitle">{{ $row['classes']->count() }} kelas</p>
                    </div>
                </div>
            </x-slot:header>
            <x-slot:actions>
                <x-ui.button size="sm" variant="secondary" :href="route('parent.progress.child', $row['child'])" arrow>Detail</x-ui.button>
            </x-slot:actions>

            @forelse ($row['classes'] as $class)
                @php($summary = $row['summaries']->get($class->id))
                <div class="grid gap-3 border-t border-line px-card py-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)] sm:items-center">
                    <div class="min-w-0">
                        <p class="font-medium text-fg">{{ $class->name }}</p>
                        <p class="text-sm text-fg-subtle">{{ $class->curriculum?->title ?? 'Kurikulum belum ditetapkan' }}</p>
                    </div>
                    @if ($summary?->hasCurriculum())
                        <x-ui.progress :value="$summary->percent()" show-value
                            :tone="$summary->percent() >= 100 ? 'success' : 'brand'"
                            label="{{ $summary->completed() }}/{{ $summary->total() }} pertemuan" />
                    @else
                        <p class="text-sm text-fg-subtle">Progres muncul setelah kurikulum ditetapkan.</p>
                    @endif
                </div>
            @empty
                <div class="border-t border-line"><x-ui.empty-state icon="academic-cap" title="Belum terdaftar di kelas mana pun." /></div>
            @endforelse
        </x-ui.card>
    @empty
        <x-ui.empty-state size="page" icon="user-group" title="Belum ada anak yang terhubung dengan akun Anda."
            description="Hubungi admin untuk menautkan akun siswa anak Anda." />
    @endforelse
</x-app-layout>
