<?php

namespace Tests\Feature\DesignSystem;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Restyled pagination views and the legacy Breeze components keep working.
 */
class PaginationAndLegacyComponentsTest extends TestCase
{
    public function test_length_aware_pagination_renders_in_indonesian(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 20), 134, 20, 2, ['path' => '/admin/users']);

        $html = (string) $paginator->links();

        $this->assertStringContainsString('Menampilkan', $html);
        $this->assertStringContainsString('21–40', $html);
        $this->assertStringContainsString('dari <strong>134</strong>', $html);
        $this->assertStringContainsString('Sebelumnya', $html);
        $this->assertStringContainsString('Berikutnya', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('data-pagination-from="21"', $html);
    }

    public function test_simple_pagination_renders(): void
    {
        $paginator = new Paginator(range(1, 10), 5, 1, ['path' => '/kelas']);

        $html = (string) $paginator->links();

        $this->assertStringContainsString('Berikutnya', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
    }

    public function test_single_page_renders_nothing(): void
    {
        $paginator = new LengthAwarePaginator(range(1, 3), 3, 20, 1);

        $this->assertSame('', trim((string) $paginator->links()));
    }

    public function test_bootstrap_and_semantic_views_are_removed(): void
    {
        foreach (['bootstrap-4', 'bootstrap-5', 'simple-bootstrap-4', 'simple-bootstrap-5', 'semantic-ui'] as $view) {
            $this->assertFileDoesNotExist(resource_path("views/vendor/pagination/{$view}.blade.php"));
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function legacyComponents(): array
    {
        return [
            'primary button' => ['<x-primary-button>Simpan</x-primary-button>', 'btn btn-primary'],
            'secondary button' => ['<x-secondary-button>Batal</x-secondary-button>', 'type="button"'],
            'danger button' => ['<x-danger-button>Hapus</x-danger-button>', 'btn btn-danger'],
            'text input' => ['<x-text-input id="email" class="mt-1 block w-full" type="email" />', 'class="input mt-1 block w-full"'],
            'input label' => ['<x-input-label for="email" :value="\'Email\'" />', 'class="label"'],
            'input error' => ['<x-input-error :messages="[\'Email wajib diisi.\']" />', 'Email wajib diisi.'],
            'session status' => ['<x-auth-session-status :status="\'Tautan terkirim.\'" />', 'alert-success'],
            'nav link' => ['<x-nav-link href="/" :active="true">Dasbor</x-nav-link>', 'aria-current="page"'],
            'responsive nav link' => ['<x-responsive-nav-link href="/" :active="false">Jadwal</x-responsive-nav-link>', 'text-fg-muted'],
            'dropdown' => ['<x-dropdown><x-slot name="trigger"><button>Menu</button></x-slot><x-slot name="content"><x-dropdown-link href="/p">Profil</x-dropdown-link></x-slot></x-dropdown>', 'dropdown-item'],
            'modal' => ['<x-modal name="hapus-akun" :show="false" focusable><p>Isi</p></x-modal>', 'open-modal'],
            'photo upload' => ['<x-photo-upload name="photo" :max-size="5120" :enable-crop="true" />', 'photoUpload'],
        ];
    }

    #[DataProvider('legacyComponents')]
    public function test_legacy_component_renders(string $template, string $expected): void
    {
        $this->assertStringContainsString($expected, Blade::render($template));
    }

    public function test_photo_upload_has_no_third_party_requests(): void
    {
        $html = Blade::render('<x-photo-upload name="photo" :enable-crop="true" current-photo="/storage/a.jpg" />');

        $this->assertStringNotContainsString('cdnjs', $html);
        $this->assertStringNotContainsString('Debug', $html);
    }

    public function test_user_avatar_keeps_its_props(): void
    {
        $user = new User(['name' => 'Budi Santoso']);
        $user->id = 7;

        $html = Blade::render('<x-user-avatar :user="$user" size="lg" gradient="green" class="gap-2" />', ['user' => $user]);

        $this->assertStringContainsString('BS', $html);
        $this->assertStringContainsString('avatar-lg', $html);
    }

    public function test_data_table_escapes_render_output_unless_htmlable(): void
    {
        $columns = [
            ['key' => 'name', 'label' => 'Nama', 'render' => fn ($row) => $row['name']],
            ['key' => 'badge', 'label' => 'Status', 'render' => fn ($row) => new HtmlString('<b>OK</b>')],
        ];

        $html = Blade::render('<x-data-table :columns="$columns" :data="$rows" :searchable="false" />', [
            'columns' => $columns,
            'rows' => collect([['name' => '<img src=x onerror=alert(1)>']]),
        ]);

        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringContainsString('<b>OK</b>', $html);
    }
}
