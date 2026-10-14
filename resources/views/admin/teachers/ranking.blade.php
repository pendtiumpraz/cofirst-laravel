@php
    $sortUrl = fn ($key) => route('admin.teachers.ranking', ['period' => $period, 'sort' => $key]);
    $columns = ['classes' => 'Kelas aktif', 'students' => 'Siswa aktif', 'meetings' => 'Pertemuan', 'reports' => 'Laporan', 'attendance' => 'Kehadiran'];
@endphp

<x-app-layout title="Kinerja pengajar">
    <x-ui.page-header title="Kinerja pengajar" subtitle="Beban mengajar, pertemuan terlaksana, laporan dan kehadiran siswa per pengajar."
        :breadcrumbs="['Admin' => route('admin.dashboard'), 'Pengguna' => route('admin.users.index'), 'Kinerja pengajar' => null]" />

    <x-ui.tabs class="mt-6" label="Periode" :active="$period" :tabs="collect($periods)->mapWithKeys(fn ($label, $key) => [$key => ['label' => $label, 'href' => route('admin.teachers.ranking', ['period' => $key, 'sort' => $sort])]])->all()" />

    <x-ui.table caption="Kinerja pengajar" mobile="stack" class="mt-6">
        <x-slot:head>
            <x-ui.th>Pengajar</x-ui.th>
            @foreach ($columns as $key => $label)
                <th scope="col" class="num" @if ($sort === $key) aria-sort="descending" @endif>
                    <a href="{{ $sortUrl($key) }}" class="th-sort" @if ($sort === $key) aria-current="true" @endif>
                        <span>{{ $label }}</span>
                        <x-ui.icon :name="$sort === $key ? 'chevron-down' : 'chevron-up-down'" />
                    </a>
                </th>
            @endforeach
        </x-slot:head>

        @foreach ($rows as $row)
            <tr>
                <td class="cell-primary" data-label="Pengajar">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$row['teacher']" size="sm" decorative />
                        <a href="{{ route('admin.users.show', $row['teacher']) }}" class="min-w-0 truncate font-medium text-fg hover:text-brand-text">{{ $row['teacher']->name }}</a>
                    </div>
                </td>
                <td class="num" data-label="Kelas aktif">{{ $row['classes'] }}</td>
                <td class="num" data-label="Siswa aktif">{{ $row['students'] }}</td>
                <td class="num" data-label="Pertemuan">{{ $row['meetings'] }}</td>
                <td class="num" data-label="Laporan">{{ $row['reports'] }}</td>
                <td class="num" data-label="Kehadiran">
                    @if ($row['attendance'] === null)
                        <span class="text-fg-subtle">—</span>
                    @else
                        <span @class(['text-danger' => $row['attendance'] < 75, 'text-fg' => $row['attendance'] >= 75])>{{ number_format($row['attendance'], 1, ',', '.') }}%</span>
                    @endif
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="presentation-chart-bar" title="Belum ada pengajar aktif." description="Tambahkan akun dengan peran Guru di menu Pengguna." />
        </x-slot:empty>
        <x-slot:footer>
            <p class="text-xs text-fg-subtle">Pertemuan = tanggal laporan pertemuan per kelas. Kehadiran = hadir + terlambat dibanding seluruh catatan absensi kelas yang diajar.</p>
        </x-slot:footer>
    </x-ui.table>
</x-app-layout>
