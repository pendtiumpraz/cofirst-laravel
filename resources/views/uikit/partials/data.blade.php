@php
    $tableSnippet = <<<'BLADE'
<x-ui.table caption="Daftar pengguna" mobile="stack">
    <x-slot:toolbar><x-ui.filter-bar placeholder="Cari nama, email…" /></x-slot:toolbar>
    <x-slot:head>
        <x-ui.th sort="name">Nama</x-ui.th>
        <x-ui.th sort="amount" numeric>Tagihan</x-ui.th>
    </x-slot:head>
    @foreach ($users as $user) <tr>…<td data-label="Tagihan" class="num">…</td></tr> @endforeach
    <x-slot:empty><x-ui.empty-state title="Belum ada pengguna." /></x-slot:empty>
    <x-slot:footer>{{ $users->withQueryString()->links() }}</x-slot:footer>
</x-ui.table>
BLADE;
    $statusTone = ['Aktif' => 'success', 'Menunggu' => 'warning', 'Terlambat' => 'danger', 'Cuti' => 'info', 'Arsip' => 'neutral'];
@endphp

<section id="data" class="scroll-mt-24 space-y-6">
    <x-ui.section-heading number="07" eyebrow="Komponen" title="Tabel & grafik"
        description="Tabel 'buku besar': tanpa zebra, angka rata kanan dan tabular, kartu bertumpuk di ponsel. Grafik mengikuti tema dan mode gelap." />

    <x-ui.table caption="Contoh daftar pengguna" mobile="stack">
        <x-slot:toolbar>
            <x-ui.filter-bar placeholder="Cari nama, email…" class="w-full" />
        </x-slot:toolbar>
        <x-slot:head>
            <x-ui.th sort="name">Nama</x-ui.th>
            <x-ui.th>Peran</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Tagihan</x-ui.th>
            <x-ui.th sort="amount" numeric default-direction="desc">Jumlah</x-ui.th>
            <x-ui.th sort="joined" numeric>Bergabung</x-ui.th>
            <x-ui.th align="right"><span class="sr-only">Aksi</span></x-ui.th>
        </x-slot:head>

        @foreach ($users as $row)
            <tr>
                <td class="cell-primary" data-label="Nama">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :name="$row['name']" size="sm" decorative />
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $row['name'] }}</p>
                            <p class="cell-secondary truncate">{{ $row['email'] }}</p>
                        </div>
                    </div>
                </td>
                <td data-label="Peran">{{ $row['role'] }}</td>
                <td data-label="Status"><x-ui.badge :tone="$statusTone[$row['status']] ?? 'neutral'" dot>{{ $row['status'] }}</x-ui.badge></td>
                <td data-label="Tagihan"><span class="font-mono text-xs text-fg-muted">{{ $row['invoice'] }}</span></td>
                <td class="num" data-label="Jumlah">{{ $row['amount'] ? 'Rp '.number_format($row['amount'], 0, ',', '.') : '—' }}</td>
                <td class="num" data-label="Bergabung">{{ \Illuminate\Support\Carbon::parse($row['joined'])->locale('id')->translatedFormat('d M Y') }}</td>
                <td class="cell-actions">
                    <div class="inline-flex items-center gap-1">
                        <x-ui.button variant="ghost" size="xs" icon="eye" label="Lihat {{ $row['name'] }}" />
                        <x-ui.dropdown align="right" width="48">
                            <x-slot:trigger>
                                <button type="button" class="btn btn-ghost btn-icon btn-xs" aria-label="Menu {{ $row['name'] }}" aria-haspopup="true" x-bind:aria-expanded="open.toString()">
                                    <x-ui.icon name="ellipsis-horizontal" />
                                </button>
                            </x-slot:trigger>
                            <x-ui.dropdown-item icon="pencil-square" href="#data">Ubah</x-ui.dropdown-item>
                            <x-ui.dropdown-item icon="archive-box">Arsipkan</x-ui.dropdown-item>
                            <div class="dropdown-divider"></div>
                            <x-ui.dropdown-item icon="trash" danger x-on:click="$dispatch('open-modal', 'demo-hapus')">Hapus</x-ui.dropdown-item>
                        </x-ui.dropdown>
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot:empty>
            <x-ui.empty-state icon="magnifying-glass" title="Tidak ditemukan hasil."
                description="Coba kata kunci lain atau hapus filter.">
                <x-slot:actions><x-ui.button variant="secondary" size="sm" :href="url()->current()">Hapus filter</x-ui.button></x-slot:actions>
            </x-ui.empty-state>
        </x-slot:empty>

        <x-slot:footer>{{ $users->links() }}</x-slot:footer>
    </x-ui.table>

    <pre class="card card-code overflow-x-auto p-4"><code>{{ $tableSnippet }}</code></pre>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Pendapatan" subtitle="Batang · bulan berjalan disorot (highlight-last)">
            <x-ui.chart type="bar" format="idr-compact" highlight-last
                label="Pendapatan Oktober Rp 48,2 juta, naik 11% dari September."
                :labels="$revenue['labels']"
                :datasets="[['label' => 'Pendapatan', 'data' => $revenue['data']]]" />
        </x-ui.card>

        <x-ui.card title="Kehadiran" subtitle="Garis · target sebagai seri kedua">
            <x-ui.chart type="line" format="percent" :height="280"
                label="Kehadiran naik ke 96% pada minggu ke-8, di atas target 90%."
                :labels="$attendance['labels']"
                :options="['scales' => ['y' => ['beginAtZero' => false, 'suggestedMin' => 80, 'suggestedMax' => 100]]]"
                :datasets="[
                    ['label' => 'Kehadiran', 'data' => $attendance['present']],
                    ['label' => 'Target', 'data' => $attendance['target'], 'tone' => 'warning', 'fill' => false, 'borderDash' => [4, 4]],
                ]" />
        </x-ui.card>

        <x-ui.card title="Status pembayaran" subtitle="Donat · maks. 4 segmen">
            <x-ui.chart type="doughnut" :height="240"
                label="124 tagihan lunas, 18 menunggu, 9 jatuh tempo, 4 terlambat."
                :labels="$payments['labels']"
                :datasets="[['label' => 'Tagihan', 'data' => $payments['data']]]" />
        </x-ui.card>

        <x-ui.card title="Pendaftaran per program" subtitle="Batang horizontal, diurutkan">
            <x-ui.chart type="bar" horizontal :height="240"
                label="Python Dasar paling diminati dengan 48 siswa."
                :labels="['Python Dasar', 'Scratch Junior', 'Web Developer', 'Robotika']"
                :datasets="[['label' => 'Siswa', 'data' => [48, 37, 22, 14]]]" />
        </x-ui.card>
    </div>
</section>
