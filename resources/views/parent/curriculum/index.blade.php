<x-app-layout title="Kurikulum anak">
    <x-ui.page-header title="Kurikulum anak"
        subtitle="Apa yang dipelajari anak Anda di setiap kelas, dan sudah sampai pertemuan ke berapa." />

    @forelse ($rows as $row)
        <section class="mb-10" aria-labelledby="anak-{{ $row['child']->id }}">
            <div class="mb-4 flex items-center gap-3">
                <x-ui.avatar :user="$row['child']" size="md" decorative />
                <h2 id="anak-{{ $row['child']->id }}" class="section-title">{{ $row['child']->name }}</h2>
            </div>

            @if ($row['classes']->isEmpty())
                <x-ui.empty-state icon="academic-cap" title="Belum terdaftar di kelas mana pun." class="card" />
            @else
                <div class="grid gap-gutter md:grid-cols-2">
                    @foreach ($row['classes'] as $class)
                        @php($summary = $row['summaries']->get($class->id))
                        @php($next = $summary?->nextMeeting())
                        <x-ui.card as="article" :title="$class->name" :subtitle="$class->curriculum?->title ?? 'Kurikulum belum ditetapkan'" level="3">
                            @if ($summary?->hasCurriculum())
                                <x-ui.progress :value="$summary->percent()" show-value
                                    :tone="$summary->percent() >= 100 ? 'success' : 'brand'"
                                    label="{{ $summary->completed() }} dari {{ $summary->total() }} pertemuan selesai" />
                                @if ($next)
                                    <p class="mt-3 text-sm text-fg-muted">Berikutnya: <span class="font-medium text-fg">Pertemuan {{ $next->meeting_number }} · {{ $next->title }}</span></p>
                                @endif
                            @endif
                            <p class="mt-3 text-sm text-fg-subtle">{{ $class->course?->name }}@if ($class->teachers->isNotEmpty()) · {{ $class->teachers->pluck('name')->join(', ') }}@endif</p>
                            <x-slot:footer>
                                <x-ui.button size="sm" icon="queue-list" :href="route('parent.curriculum.child-progress', [$row['child'], $class])">Lihat kurikulum</x-ui.button>
                            </x-slot:footer>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <x-ui.empty-state size="page" icon="user-group" title="Belum ada anak yang terhubung dengan akun Anda."
            description="Hubungi admin untuk menautkan akun siswa anak Anda." />
    @endforelse
</x-app-layout>
