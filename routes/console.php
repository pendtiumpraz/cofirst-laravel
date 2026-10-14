<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Teacher portal: close temporary handovers (guru pengganti) whose period has ended,
// so the substitute stops seeing the class. Logic lives in HandoverWorkflow.
Artisan::command('teacher:complete-handovers', function () {
    $count = app(\App\Services\Teacher\HandoverWorkflow::class)->completeExpired();
    $this->info("{$count} serah terima sementara diselesaikan.");
})->purpose('Selesaikan serah terima sementara yang periodenya sudah berakhir');

\Illuminate\Support\Facades\Schedule::command('teacher:complete-handovers')->hourly()->withoutOverlapping();

// Academic: rebuild class_progress from the existing meeting reports, move legacy
// material files off the public disk, and purge old material-access rows (UU PDP
// storage limitation). Logic lives in App\Services\Academic.
Artisan::command('academic:rebuild-progress {--class= : Hanya untuk ID kelas ini}', function () {
    $count = app(\App\Services\Academic\ProgressService::class)->rebuildFromReports($this->option('class') ? (int) $this->option('class') : null);
    $this->info("{$count} berita acara diproses; progres pertemuan diperbarui.");
})->purpose('Bangun ulang progres pertemuan siswa dari berita acara');

Artisan::command('academic:secure-material-files', function () {
    $count = app(\App\Services\Academic\MaterialFileService::class)->secureAllLegacyFiles();
    $this->info("{$count} berkas materi dipindahkan ke penyimpanan privat.");
})->purpose('Pindahkan berkas materi lama dari disk publik ke disk privat');

Artisan::command('academic:purge-material-access', function () {
    $count = app(\App\Services\Academic\MaterialAccessService::class)->purgeExpired();
    $this->info("{$count} catatan akses materi lama dihapus.");
})->purpose('Hapus riwayat akses materi yang melewati masa retensi');

\Illuminate\Support\Facades\Schedule::command('academic:purge-material-access')->dailyAt('02:30')->withoutOverlapping();

// PDP consent: expire parental-consent links older than 7 days and erase the child
// registrations that never got guardian consent (UU PDP Pasal 25 & 43). Logic lives in
// App\Services\Privacy\ParentalConsentService.
Artisan::command('privacy:expire-parental-consents', function () {
    $count = app(\App\Services\Privacy\ParentalConsentService::class)->expireStale();
    $this->info("{$count} permintaan persetujuan orang tua kedaluwarsa; pendaftaran terkait dihapus.");
})->purpose('Kedaluwarsakan permintaan persetujuan orang tua/wali dan hapus pendaftaran anak yang tidak disetujui');

\Illuminate\Support\Facades\Schedule::command('privacy:expire-parental-consents')->hourly()->withoutOverlapping();
