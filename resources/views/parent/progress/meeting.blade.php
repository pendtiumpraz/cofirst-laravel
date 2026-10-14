@php($f = \App\Services\Academic\Format::class)
@php($rectifyUrl = collect(['account.privacy.index', 'privacy.dsr.create'])->first(fn ($name) => Route::has($name)))
<x-app-layout :title="'Pertemuan '.$report->meeting_number.' · '.$child->name">
    <x-ui.page-header :title="'Pertemuan '.$report->meeting_number" :eyebrow="$child->name.' · '.($report->className?->name ?? '')"
        :subtitle="$f::date($report->report_date, 'l, d F Y').' · '.($report->teacher?->name ?? '')"
        :breadcrumbs="['Progres anak' => route('parent.progress.index'), $child->name => route('parent.progress.child', $child), 'Catatan pertemuan' => route('parent.progress.meetings', $child), 'Pertemuan '.$report->meeting_number => null]" />

    <div class="max-w-4xl space-y-6">
        @include('class-reports.partials.report-body', ['showInternal' => false, 'showMedia' => true])

        <p class="flex items-start gap-2 text-sm text-fg-subtle">
            <x-ui.icon name="shield-check" class="mt-0.5 shrink-0" />
            <span>
                Ada yang kurang tepat? Anda berhak meminta koreksi data anak (UU No. 27/2022).
                @if ($rectifyUrl)
                    <a href="{{ route($rectifyUrl) }}" class="link">Ajukan koreksi</a>
                @else
                    Hubungi pengajar lewat chat atau admin sekolah.
                @endif
            </span>
        </p>
    </div>
</x-app-layout>
