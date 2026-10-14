<?php

namespace Tests\Feature\DesignSystem;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every <x-ui.*> component renders with typical props (no exceptions) and
 * produces the expected landmark markup.
 */
class UiComponentsRenderTest extends TestCase
{
    /**
     * Typical usage of each component and a string the output must contain.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function components(): array
    {
        return [
            'icon' => ['<x-ui.icon name="academic-cap" size="20" />', 'size-5'],
            'icon unknown name renders nothing' => ['<span>x</span><x-ui.icon name="does-not-exist-xyz" />', '<span>x</span>'],
            'button' => ['<x-ui.button icon="plus">Tambah siswa</x-ui.button>', 'btn btn-primary'],
            'button link' => ['<x-ui.button variant="secondary" href="/kelas" arrow>Lihat kelas</x-ui.button>', 'href="/kelas"'],
            'button icon-only' => ['<x-ui.button variant="ghost" icon="pencil-square" label="Ubah" size="sm" />', 'aria-label="Ubah"'],
            'button loading' => ['<x-ui.button loading loading-text="Menyimpan…">Simpan</x-ui.button>', 'aria-busy="true"'],
            'button alpine loading' => ['<x-ui.button type="submit" loading-when="isSubmitting" loading-text="Menyimpan…">Simpan</x-ui.button>', 'x-bind:disabled="isSubmitting"'],
            'card' => ['<x-ui.card title="Kelas berikutnya" subtitle="Senin" eyebrow="Hari ini"><x-slot:actions>A</x-slot:actions> Isi <x-slot:footer>F</x-slot:footer></x-ui.card>', 'card-title'],
            'card flush' => ['<x-ui.card title="Daftar" padding="none"><ul><li>a</li></ul></x-ui.card>', 'card-header-flush'],
            'card interactive' => ['<x-ui.card title="Buka" href="/x">Isi</x-ui.card>', 'card-link'],
            'stat' => ['<x-ui.stat label="Pendapatan" value="Rp 48,2 jt" trend="up" delta="12%" hint="vs bulan lalu" icon="banknotes" tone="success" />', 'stat-delta-success'],
            'stat empty value' => ['<x-ui.stat label="Kehadiran" />', 'Belum ada data'],
            'metric strip' => ['<x-ui.metric-strip :stats="[[\'label\' => \'Siswa aktif\', \'value\' => \'312\'], [\'label\' => \'Kelas\', \'value\' => \'14\']]" />', 'data-cols="2"'],
            'badge' => ['<x-ui.badge tone="success" dot>Lunas</x-ui.badge>', 'badge-success'],
            'badge mono' => ['<x-ui.badge mono>INV-2026-0142</x-ui.badge>', 'badge-code'],
            'alert' => ['<x-ui.alert tone="danger" title="Gagal" dismissible>Coba lagi.</x-ui.alert>', 'role="alert"'],
            'page header' => ['<x-ui.page-header title="Pengguna" subtitle="Kelola akun" :breadcrumbs="[[\'label\' => \'Admin\', \'url\' => \'/admin\'], [\'label\' => \'Pengguna\']]"><x-slot:actions>B</x-slot:actions></x-ui.page-header>', '<h1 class="page-title">'],
            'breadcrumbs' => ['<x-ui.breadcrumbs :items="[\'Admin\' => \'/admin\', \'Pengguna\' => null]" />', 'aria-current="page"'],
            'section heading' => ['<x-ui.section-heading title="Perlu tindakan" number="01" eyebrow="Ringkasan" />', 'section-num'],
            'table' => ['<x-ui.table caption="Daftar"><x-slot:head><x-ui.th>Nama</x-ui.th></x-slot:head><tr><td data-label="Nama">Andi</td></tr></x-ui.table>', 'table-cf'],
            'table empty' => ['<x-ui.table caption="Daftar" mobile="stack"><x-slot:head><x-ui.th>Nama</x-ui.th></x-slot:head><x-slot:empty>Belum ada data.</x-slot:empty></x-ui.table>', 'Belum ada data.'],
            'th sortable' => ['<x-ui.th sort="name">Nama</x-ui.th>', 'aria-sort="none"'],
            'empty state' => ['<x-ui.empty-state icon="calendar" title="Belum ada kelas." description="Jadwal muncul nanti." hint="3×24 jam"><x-slot:actions>A</x-slot:actions></x-ui.empty-state>', 'empty-state-title'],
            'field' => ['<x-ui.field label="Email" for="email" hint="Bantuan" optional><x-ui.input name="email" type="email" /></x-ui.field>', 'aria-describedby="email-hint"'],
            'input addon' => ['<x-ui.input name="amount" prefix="Rp" value="1000" />', 'input-addon'],
            'input icon' => ['<x-ui.input name="q" type="search" icon="magnifying-glass" />', 'input-icon'],
            'select' => ['<x-ui.select name="status" :options="[\'active\' => \'Aktif\', \'inactive\' => \'Nonaktif\']" value="inactive" placeholder="Pilih" />', '<option value="inactive" selected'],
            'select optgroups' => ['<x-ui.select name="kelas" :options="[\'Anak\' => [\'s\' => \'Scratch\'], \'Remaja\' => [\'p\' => \'Python\']]" />', '<optgroup label="Remaja">'],
            'textarea' => ['<x-ui.textarea name="notes" value="Catatan" rows="3" />', '>Catatan</textarea>'],
            'checkbox' => ['<x-ui.checkbox name="consent" label="Saya setuju" description="v2.1" unchecked-value="0" />', 'type="hidden" name="consent" value="0"'],
            'checkbox array' => ['<x-ui.checkbox name="roles[]" value="teacher" checked label="Guru" />', 'id="roles_teacher"'],
            'toggle' => ['<x-ui.toggle name="email_notif" label="Email" checked />', 'role="switch"'],
            'file' => ['<x-ui.file name="proof" accept="image/*" :max-kb="2048" hint="maks. 2 MB" preview />', 'cfFileDrop'],
            'radio cards' => ['<x-ui.radio-cards name="type" legend="Jenis" value="access" :options="[\'access\' => [\'label\' => \'Akses\', \'description\' => \'Salinan data\'], \'erasure\' => \'Hapus\']" />', 'value="access"'],
            'modal' => ['<x-ui.modal name="tambah-kelas" title="Tambah kelas"><x-slot:trigger>T</x-slot:trigger> Isi <x-slot:footer>F</x-slot:footer></x-ui.modal>', 'aria-modal="true"'],
            'dropdown' => ['<x-ui.dropdown label="Ekspor" icon="arrow-down-tray"><x-ui.dropdown-item href="/csv" icon="table-cells">CSV</x-ui.dropdown-item><x-ui.dropdown-item danger>Hapus</x-ui.dropdown-item></x-ui.dropdown>', 'dropdown-item-danger'],
            'dropdown item post' => ['<x-ui.dropdown-item href="/logout" method="post">Keluar</x-ui.dropdown-item>', 'method="POST"'],
            'tabs' => ['<x-ui.tabs group="akun" active="profil" :tabs="[\'profil\' => \'Profil\', \'keamanan\' => [\'label\' => \'Keamanan\', \'count\' => 2]]"><x-ui.tab-panel name="profil">P</x-ui.tab-panel><x-ui.tab-panel name="keamanan">K</x-ui.tab-panel></x-ui.tabs>', 'role="tablist"'],
            'tabs nav' => ['<x-ui.tabs active="a" :tabs="[\'a\' => [\'label\' => \'A\', \'href\' => \'/a\'], \'b\' => [\'label\' => \'B\', \'href\' => \'/b\']]" />', 'aria-current="page"'],
            'avatar initials' => ['<x-ui.avatar name="渡辺 健二" size="lg" />', '渡健'],
            'progress' => ['<x-ui.progress :value="72" label="Progres" show-value tone="success" />', 'aria-valuenow="72"'],
            'progress ring' => ['<x-ui.progress-ring :value="40" label="Kehadiran" />', 'progress-ring-marker'],
            'confirm delete' => ['<x-ui.confirm-delete action="/admin/kelas/1" title="Hapus kelas?" />', 'name="_method" value="DELETE"'],
            'confirm delete typed' => ['<x-ui.confirm-delete action="/akun" confirm-phrase="HAPUS" variant="danger" label="Hapus akun" />', 'x-model="typed"'],
            'filter bar' => ['<x-ui.filter-bar placeholder="Cari nama…"><x-ui.select name="role" :options="[\'a\' => \'A\']" placeholder="Semua" /></x-ui.filter-bar>', 'data-search-shortcut'],
            'toast' => ['<x-ui.toast />', 'toast-region'],
            'chart' => ['<x-ui.chart type="bar" label="Pendapatan naik" format="idr" :labels="[\'Sep\', \'Okt\']" :datasets="[[\'label\' => \'Pendapatan\', \'data\' => [1000, 2500]]]" />', 'data-cf-chart-config'],
            'skeleton' => ['<x-ui.skeleton variant="rows" :rows="3" />', 'Memuat…'],
            'kbd' => ['<x-ui.kbd>Ctrl K</x-ui.kbd>', '<kbd class="kbd">'],
            'divider' => ['<x-ui.divider label="atau" />', 'divider-label'],
            'color mode script' => ['<x-ui.color-mode-script />', 'cf-color-mode'],
            'color mode toggle' => ['<x-ui.color-mode-toggle />', 'role="radiogroup"'],
        ];
    }

    #[DataProvider('components')]
    public function test_component_renders(string $template, string $expected): void
    {
        $html = Blade::render($template);

        $this->assertStringContainsString($expected, $html);
    }

    public function test_avatar_uses_user_model_and_stable_tint(): void
    {
        $user = new User(['name' => 'Siti Nurhaliza']);
        $user->id = 42;

        $first = Blade::render('<x-ui.avatar :user="$user" />', ['user' => $user]);
        $second = Blade::render('<x-ui.avatar :user="$user" />', ['user' => $user]);

        $this->assertStringContainsString('SN', $first);
        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression('/avatar-tint-[0-5]/', $first);
    }

    public function test_field_and_input_show_validation_state(): void
    {
        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([
            'email' => ['Email wajib diisi.'],
        ])));

        $html = Blade::render('<x-ui.field label="Email" for="email"><x-ui.input name="email" /></x-ui.field>');

        $this->assertStringContainsString('Email wajib diisi.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('email-error', $html);
    }

    public function test_toast_renders_flash_and_validation_summary(): void
    {
        session()->flash('success', 'Kelas berhasil disimpan.');
        View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([
            'name' => ['Nama wajib diisi.'],
            'email' => ['Email wajib diisi.'],
        ])));

        $html = Blade::render('<x-ui.toast />');

        $this->assertStringContainsString('Kelas berhasil disimpan.', $html);
        $this->assertStringContainsString('Ada 2 isian yang perlu diperbaiki.', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
    }

    public function test_input_prefers_old_input_over_value(): void
    {
        $session = $this->app['session.store'];
        $session->put('_old_input', ['name' => 'Nilai lama']);
        request()->setLaravelSession($session);

        $html = Blade::render('<x-ui.input name="name" value="Nilai baru" />');

        $this->assertStringContainsString('value="Nilai lama"', $html);
    }

    public function test_password_input_never_echoes_a_value(): void
    {
        $html = Blade::render('<x-ui.input name="password" type="password" value="rahasia" />');

        $this->assertStringNotContainsString('rahasia', $html);
    }

    public function test_th_sort_link_keeps_query_and_toggles_direction(): void
    {
        $this->app->instance('request', Request::create('/admin/users', 'GET', [
            'search' => 'andi', 'sort' => 'name', 'direction' => 'asc', 'page' => 3,
        ]));

        $html = Blade::render('<x-ui.th sort="name">Nama</x-ui.th>');

        $this->assertStringContainsString('aria-sort="ascending"', $html);
        $this->assertStringContainsString('direction=desc', $html);
        $this->assertStringContainsString('search=andi', $html);
        $this->assertStringNotContainsString('page=3', $html);
        $this->assertStringContainsString('data-sortable="false"', $html);
    }

    public function test_chart_config_is_json_encoded_safely(): void
    {
        $html = Blade::render('<x-ui.chart label="x" :labels="[\'</script><b>\']" :datasets="[[\'label\' => \'A\', \'data\' => [1]]]" />');

        $this->assertStringNotContainsString('</script><b>', $html);
        $this->assertStringContainsString('Lihat tabel', $html);
    }
}
