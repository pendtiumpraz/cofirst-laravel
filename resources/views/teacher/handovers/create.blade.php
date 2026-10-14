<x-app-layout title="Ajukan Serah Terima">
    <x-ui.page-header title="Ajukan serah terima"
        subtitle="Permintaan dikirim ke pengajar penerima. Kelas baru berpindah setelah ia menyetujuinya."
        :breadcrumbs="['Serah terima' => route('teacher.handovers.index'), 'Ajukan' => null]" />

    @if ($classes->isEmpty())
        <x-ui.empty-state size="page" icon="rectangle-group" title="Belum ada kelas yang dapat diserahterimakan."
            description="Serah terima hanya tersedia untuk kelas yang sedang berlangsung atau direncanakan.">
            <x-slot:actions><x-ui.button variant="secondary" :href="route('teacher.handovers.index')">Kembali</x-ui.button></x-slot:actions>
        </x-ui.empty-state>
    @else
        @include('teacher.handovers.partials.form', [
            'action' => route('teacher.handovers.store'),
            'method' => 'POST',
            'submitLabel' => 'Kirim permintaan',
            'cancelUrl' => route('teacher.handovers.index'),
        ])
    @endif
</x-app-layout>
