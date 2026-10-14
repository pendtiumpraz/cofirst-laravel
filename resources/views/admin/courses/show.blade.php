@php
    use App\Services\Admin\AdminClock;
    use Illuminate\Support\Facades\Route;

    $levels = App\Http\Requests\Admin\CourseRequest::LEVELS;
    $statusTone = ['active' => 'success', 'planned' => 'info', 'completed' => 'neutral', 'cancelled' => 'danger'];
    $benefits = collect(preg_split('/\r\n|\r|\n/', (string) $course->benefits))->map(fn ($b) => trim($b, " \t-•"))->filter()->values();
@endphp

<x-app-layout :title="$course->name">
    <x-ui.page-header :title="$course->name" :breadcrumbs="['Admin' => route('admin.dashboard'), 'Kursus' => route('admin.courses.index'), $course->name => null]">
        <x-slot:badge>
            @if ($course->trashed())
                <x-ui.badge tone="neutral" dot>Diarsipkan</x-ui.badge>
            @elseif ($course->is_active)
                <x-ui.badge tone="success" dot>Aktif</x-ui.badge>
            @else
                <x-ui.badge tone="warning" dot>Nonaktif</x-ui.badge>
            @endif
        </x-slot:badge>
        <x-slot:meta>
            <span>/{{ $course->slug }}</span>
            <span>{{ $levels[$course->level] ?? $course->level }}</span>
            <span>{{ $course->duration_weeks }} minggu{{ $course->duration_hours ? ' · '.$course->duration_hours.' jam' : '' }}</span>
            <span>{{ $course->formatted_price }}</span>
        </x-slot:meta>
        <x-slot:actions>
            @if ($course->trashed())
                @can('delete', $course)
                    <form method="POST" action="{{ route('admin.courses.restore', $course) }}">
                        @csrf
                        <x-ui.button type="submit" icon="arrow-uturn-left">Pulihkan</x-ui.button>
                    </form>
                @endcan
            @else
                @can('create', App\Models\ClassName::class)
                    <x-ui.button variant="secondary" icon="plus" :href="route('admin.classes.create', ['course' => $course->id])">Buat kelas</x-ui.button>
                @endcan
                @can('update', $course)
                    <x-ui.button icon="pencil-square" :href="route('admin.courses.edit', $course)">Ubah</x-ui.button>
                @endcan
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.metric-strip class="mt-6" label="Statistik kursus" :stats="[
        ['label' => 'Total pendaftaran', 'value' => number_format($stats['enrollments'], 0, ',', '.'), 'icon' => 'clipboard-document-check'],
        ['label' => 'Siswa aktif', 'value' => number_format($stats['active_students'], 0, ',', '.'), 'icon' => 'academic-cap'],
        ['label' => 'Kelas berjalan/direncanakan', 'value' => (string) $stats['running_classes'], 'icon' => 'rectangle-group'],
        ['label' => 'Pendapatan bersih', 'value' => AdminClock::rupiahCompact($stats['revenue']), 'hint' => AdminClock::rupiah($stats['revenue']), 'icon' => 'banknotes'],
    ]" />

    <x-ui.tabs class="mt-8" group="kursus" active="ringkasan" label="Bagian detail kursus" :tabs="[
        'ringkasan' => ['label' => 'Ringkasan', 'icon' => 'document-text'],
        'kelas' => ['label' => 'Kelas', 'icon' => 'rectangle-group', 'count' => $classes->count()],
        'kurikulum' => ['label' => 'Kurikulum', 'icon' => 'queue-list', 'count' => $curriculums->count()],
    ]">
        <x-ui.tab-panel name="ringkasan">
            <div class="grid grid-cols-1 gap-gutter lg:grid-cols-3">
                <x-ui.card title="Deskripsi" class="lg:col-span-2">
                    <p class="max-w-measure whitespace-pre-line text-fg-muted">{{ $course->description }}</p>
                    @if ($benefits->isNotEmpty())
                        <h3 class="eyebrow mb-3 mt-6">Manfaat</h3>
                        <ul class="space-y-2" role="list">
                            @foreach ($benefits as $benefit)
                                <li class="flex gap-2 text-sm text-fg">
                                    <x-ui.icon name="check-circle" size="16" class="mt-0.5 text-success" />
                                    <span>{{ $benefit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.card>
                <x-ui.card title="Sampul">
                    @if ($course->thumbnail)
                        <img src="{{ asset('storage/'.$course->thumbnail) }}" alt="Sampul {{ $course->name }}" class="aspect-video w-full rounded-md border border-line object-cover">
                    @else
                        <x-ui.empty-state icon="photo" title="Belum ada gambar sampul." />
                    @endif
                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-fg-muted">Dibuat</dt><dd class="text-fg">{{ AdminClock::format($course->created_at) }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-fg-muted">Diperbarui</dt><dd class="text-fg">{{ AdminClock::format($course->updated_at) }}</dd></div>
                    </dl>
                </x-ui.card>
            </div>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="kelas">
            <x-ui.table caption="Kelas kursus ini" mobile="stack">
                <x-slot:head>
                    <x-ui.th>Kelas</x-ui.th>
                    <x-ui.th>Pengajar</x-ui.th>
                    <x-ui.th>Status</x-ui.th>
                    <x-ui.th numeric>Siswa</x-ui.th>
                    <x-ui.th numeric>Periode</x-ui.th>
                </x-slot:head>
                @foreach ($classes as $class)
                    <tr>
                        <td class="cell-primary" data-label="Kelas">
                            <a href="{{ route('admin.classes.show', $class) }}" class="font-medium text-fg hover:text-brand-text">{{ $class->name }}</a>
                            <p class="cell-secondary">{{ $class->type_label }}</p>
                        </td>
                        <td data-label="Pengajar">{{ $class->teachers->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td data-label="Status"><x-ui.badge :tone="$statusTone[$class->status] ?? 'neutral'" dot>{{ $class->status_label }}</x-ui.badge></td>
                        <td class="num" data-label="Siswa">{{ $class->seatsTaken() }}/{{ $class->max_students }}</td>
                        <td class="num" data-label="Periode">{{ AdminClock::format($class->start_date, 'j M') }} – {{ AdminClock::format($class->end_date, 'j M Y') }}</td>
                    </tr>
                @endforeach
                <x-slot:empty>
                    <x-ui.empty-state icon="rectangle-group" title="Belum ada kelas untuk kursus ini.">
                        @can('create', App\Models\ClassName::class)
                            <x-slot:actions><x-ui.button size="sm" icon="plus" :href="route('admin.classes.create', ['course' => $course->id])">Buat kelas</x-ui.button></x-slot:actions>
                        @endcan
                    </x-ui.empty-state>
                </x-slot:empty>
            </x-ui.table>
        </x-ui.tab-panel>

        <x-ui.tab-panel name="kurikulum">
            <x-ui.card padding="none" title="Kurikulum">
                @if (Route::has('admin.curriculums.create'))
                    <x-slot:actions><x-ui.button variant="link" size="sm" arrow :href="route('admin.curriculums.create')">Tambah kurikulum</x-ui.button></x-slot:actions>
                @endif
                @if ($curriculums->isEmpty())
                    <x-ui.empty-state icon="queue-list" title="Belum ada kurikulum." description="Kelas tetap bisa dibuat tanpa kurikulum dan ditautkan nanti." />
                @else
                    <ul class="divide-y divide-line" role="list">
                        @foreach ($curriculums as $curriculum)
                            <li class="flex items-center justify-between gap-3 px-card py-3">
                                <div class="min-w-0">
                                    @if (Route::has('admin.curriculums.show'))
                                        <a href="{{ route('admin.curriculums.show', $curriculum) }}" class="font-medium text-fg hover:text-brand-text">{{ $curriculum->title }}</a>
                                    @else
                                        <span class="font-medium text-fg">{{ $curriculum->title }}</span>
                                    @endif
                                    @if ($curriculum->duration_weeks)
                                        <p class="text-xs text-fg-subtle">{{ $curriculum->duration_weeks }} minggu</p>
                                    @endif
                                </div>
                                <x-ui.badge :tone="$curriculum->status === 'active' ? 'success' : 'neutral'">{{ $curriculum->status === 'active' ? 'Aktif' : ucfirst((string) $curriculum->status) }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </x-ui.tab-panel>
    </x-ui.tabs>

    @can('delete', $course)
        @unless ($course->trashed())
            <x-ui.card variant="callout" class="mt-10" title="Arsipkan kursus" style="--callout-c: var(--cf-danger)"
                subtitle="Menyembunyikan kursus dari katalog. Riwayat kelas, kurikulum dan pembayaran tetap tersimpan.">
                <x-ui.confirm-delete :action="route('admin.courses.destroy', $course)" variant="danger" icon="archive-box" label="Arsipkan kursus"
                    title="Arsipkan kursus ini?" confirm-text="Ya, arsipkan" confirm-phrase="ARSIPKAN"
                    message="Kursus dengan kelas yang masih direncanakan atau berlangsung tidak dapat diarsipkan." />
            </x-ui.card>
        @endunless
    @endcan
</x-app-layout>
