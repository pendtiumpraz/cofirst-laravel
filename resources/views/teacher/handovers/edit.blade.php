<x-app-layout title="Ubah Serah Terima">
    <x-ui.page-header title="Ubah permintaan serah terima"
        subtitle="Permintaan masih menunggu jawaban, jadi Anda masih dapat mengubahnya."
        :breadcrumbs="['Serah terima' => route('teacher.handovers.index'), $handover->className?->name ?? 'Permintaan' => route('teacher.handovers.show', $handover), 'Ubah' => null]" />

    @include('teacher.handovers.partials.form', [
        'action' => route('teacher.handovers.update', $handover),
        'method' => 'PUT',
        'submitLabel' => 'Simpan perubahan',
        'cancelUrl' => route('teacher.handovers.show', $handover),
    ])
</x-app-layout>
