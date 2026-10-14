<?php

namespace App\Http\Controllers;

use App\Support\Navigation\Appearance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

/**
 * Progressive Web App endpoints: the web app manifest and the offline page.
 *
 * Both responses are anonymous (no personal data) and explicitly public so the
 * service worker (public/sw.js) may cache the offline page.
 */
class PwaController extends Controller
{
    /**
     * GET /manifest.webmanifest — built from the pwa.* settings and the active theme/logo.
     */
    public function manifest(): JsonResponse
    {
        abort_unless(setting('pwa.enabled'), 404);

        $theme = (string) setting('appearance.theme', 'a');
        $colors = Appearance::colors($theme);
        $logo = Appearance::logoVariant(null, $theme);

        $manifest = [
            'id' => '/',
            'name' => (string) setting('pwa.name', 'Coding First'),
            'short_name' => (string) setting('pwa.short_name', 'CoFirst'),
            'description' => (string) setting('pwa.description', ''),
            'lang' => 'id',
            'dir' => 'ltr',
            'start_url' => '/dashboard?source=pwa',
            'scope' => '/',
            'display' => (string) setting('pwa.display', 'standalone'),
            'background_color' => $this->backgroundColor($colors['canvas']),
            'theme_color' => $colors['primary'],
            'categories' => ['education'],
            'icons' => $this->icons($logo),
            'shortcuts' => $this->shortcuts($logo),
        ];

        return response()
            ->json($manifest, 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=600');
    }

    /**
     * GET /offline — branded fallback page served by the service worker when offline.
     */
    public function offline(): Response
    {
        return response()
            ->view('pwa.offline')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Icons of the active logo variant: any (192/512), maskable (192/512), monochrome.
     *
     * @return array<int, array<string, string>>
     */
    private function icons(string $logo): array
    {
        $base = 'brand/'.$logo.'/';

        return [
            ['src' => asset($base.'icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => asset($base.'icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => asset($base.'maskable-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => asset($base.'maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ['src' => asset($base.'monochrome.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'monochrome'],
        ];
    }

    /**
     * Shortcuts to areas every role has (role-specific pages would 403 for others).
     *
     * @return array<int, array<string, mixed>>
     */
    private function shortcuts(string $logo): array
    {
        $icon = [['src' => asset('brand/'.$logo.'/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']];
        $candidates = [
            ['Beranda', 'Dasbor sesuai peran Anda', 'dashboard'],
            ['Pesan', 'Percakapan dengan guru dan admin', 'chat.index'],
            ['Pencapaian', 'Poin, lencana dan papan peringkat', 'gamification.index'],
            ['Privasi & Data Saya', 'Persetujuan dan permintaan data pribadi', 'account.privacy.index'],
        ];

        return collect($candidates)
            ->filter(fn ($c) => Route::has($c[2]))
            ->map(fn ($c) => [
                'name' => $c[0],
                'short_name' => $c[0],
                'description' => $c[1],
                'url' => route($c[2], [], false).'?source=pwa-shortcut',
                'icons' => $icon,
            ])
            ->values()
            ->all();
    }

    /**
     * pwa.background_color unless it is empty, 'auto' or the untouched registry default.
     */
    private function backgroundColor(string $themeCanvas): string
    {
        $value = (string) setting('pwa.background_color', 'auto');
        $default = (string) (config('settings.fields')['pwa.background_color']['default'] ?? '#ffffff');
        // Reason: the registry still ships '#ffffff' as default; treat it as "follow the theme" (spec §13).
        $isAuto = $value === '' || strtolower($value) === 'auto' || strtolower($value) === strtolower($default);

        return $isAuto || ! preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $themeCanvas : $value;
    }
}
