<?php

namespace Tests\Feature\Shell;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PWA: manifest, offline page, service worker rules, brand icon set.
 */
class PwaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Roles and permissions only (idempotent; rolled back with the test transaction).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_manifest_is_valid_and_lists_icons_of_the_active_logo_variant(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertOk();
        $this->assertStringStartsWith('application/manifest+json', $response->headers->get('Content-Type'));
        $manifest = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('Coding First', $manifest['name']);
        $this->assertSame('/dashboard?source=pwa', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('id', $manifest['lang']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#1D5D80', $manifest['theme_color']);      // primary-600 of theme A
        $this->assertSame('#FAF8F4', $manifest['background_color']); // registry default → theme canvas

        $icons = collect($manifest['icons']);
        $this->assertTrue($icons->contains(fn ($i) => $i['sizes'] === '192x192' && $i['purpose'] === 'any'));
        $this->assertTrue($icons->contains(fn ($i) => $i['sizes'] === '512x512' && $i['purpose'] === 'any'));
        $this->assertTrue($icons->contains(fn ($i) => $i['sizes'] === '512x512' && $i['purpose'] === 'maskable'));
        $icons->each(fn ($i) => $this->assertStringContainsString('/brand/a/', $i['src']));
        $icons->each(fn ($i) => $this->assertFileExists(public_path(parse_url($i['src'], PHP_URL_PATH))));
        $this->assertNotEmpty($manifest['shortcuts']);
    }

    public function test_manifest_follows_theme_b_and_an_explicit_logo_variant(): void
    {
        setting()->set('appearance.theme', 'b');
        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertSame('#056A81', $manifest['theme_color']);
        $this->assertStringContainsString('/brand/b/icon-192.png', $manifest['icons'][0]['src']);

        setting()->setMany(['appearance.logo_variant' => 'a', 'pwa.background_color' => '#123456']);
        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertStringContainsString('/brand/a/', $manifest['icons'][0]['src']);
        $this->assertSame('#123456', $manifest['background_color']);
    }

    public function test_manifest_and_registration_disappear_when_pwa_is_disabled(): void
    {
        setting()->set('pwa.enabled', false);

        $this->get(route('pwa.manifest'))->assertNotFound();

        $user = User::factory()->finance()->create();
        $page = $this->actingAs($user)->get(route('finance.dashboard'));
        $page->assertOk();
        $page->assertDontSee('rel="manifest"', false);
        $page->assertDontSee('serviceWorker.register', false);
        $page->assertSee('unregister', false);
    }

    public function test_layouts_link_manifest_icons_and_register_the_service_worker(): void
    {
        $user = User::factory()->finance()->create();
        $page = $this->actingAs($user)->get(route('finance.dashboard'));

        $page->assertOk();
        $page->assertSee('rel="manifest"', false);
        $page->assertSee('serviceWorker.register', false);
        $page->assertSee('apple-mobile-web-app-capable', false);
        $page->assertSee('brand/a/apple-touch-icon.png', false);
        $page->assertSee('name="theme-color" content="#FAF8F4"', false);
        $page->assertSee('name="theme-color" content="#0F0E0D"', false);
    }

    public function test_offline_page_is_anonymous_and_cacheable(): void
    {
        $user = User::factory()->student()->create();
        $response = $this->actingAs($user)->get(route('pwa.offline'));

        $response->assertOk();
        $response->assertSee('Anda sedang offline.');
        $response->assertDontSee('csrf-token', false);
        $response->assertDontSee($user->name);
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    public function test_service_worker_never_caches_private_responses_and_clears_on_logout(): void
    {
        $path = public_path('sw.js');
        $this->assertFileExists($path);
        $sw = file_get_contents($path);

        $this->assertStringContainsString('no-store|private', $sw);
        $this->assertStringContainsString("'CF_LOGOUT'", $sw);
        $this->assertStringContainsString('caches.delete', $sw);
        $this->assertStringContainsString("const OFFLINE_URL = '/offline'", $sw);
        $this->assertMatchesRegularExpression("/const CACHE_VERSION = '[^']+'/", $sw);
    }

    public function test_brand_icon_set_exists_for_both_variants(): void
    {
        $sizes = [
            'favicon-16.png' => 16, 'favicon-32.png' => 32, 'apple-touch-icon.png' => 180,
            'icon-192.png' => 192, 'icon-512.png' => 512, 'maskable-192.png' => 192, 'maskable-512.png' => 512,
        ];

        foreach (['a', 'b'] as $variant) {
            foreach ($sizes as $file => $size) {
                $info = getimagesize(public_path("brand/{$variant}/{$file}"));
                $this->assertSame([$size, $size], [$info[0], $info[1]], "brand/{$variant}/{$file}");
            }
            foreach (['favicon.svg', 'mark.svg', 'logo.svg', 'logo-dark.svg', 'monochrome.svg', 'favicon.ico', 'og-image.png'] as $file) {
                $this->assertGreaterThan(0, filesize(public_path("brand/{$variant}/{$file}")), "brand/{$variant}/{$file}");
            }
        }

        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
        $this->assertStringContainsString('#AE4027', file_get_contents(public_path('favicon.svg')));
    }

    public function test_brand_components_resolve_variant_and_custom_logo(): void
    {
        $this->blade('<x-brand.logo variant="a" />')->assertSee('#AE4027', false)->assertSee('Coding');
        $this->blade('<x-brand.logo variant="b" type="mark" />')->assertSee('#EE4D1C', false)->assertSee('aria-label="Coding First"', false);

        setting()->set('appearance.theme', 'b');
        $this->blade('<x-brand.lockup />')->assertSee('#EE4D1C', false);

        setting()->set('appearance.custom_logo', 'branding/logo.png');
        $this->blade('<x-brand.logo />')->assertSee('storage/branding/logo.png', false)->assertDontSee('#EE4D1C', false);
    }
}
