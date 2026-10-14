<?php

namespace App\Support\Navigation;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Builds the admin "Privasi & PDP" section from the admin.privacy.* routes that
 * actually exist, so every PDP back-office page is reachable without this shell
 * hard-coding route names that the privacy module owns.
 */
final class PrivacyMenu
{
    /**
     * Known route segments → [label, heroicon]; order here is the menu order.
     */
    private const KNOWN = [
        'dashboard' => ['Dasbor PDP', 'shield-check'],
        'requests' => ['Permintaan Data', 'inbox-stack'],
        'dsr' => ['Permintaan Data', 'inbox-stack'],
        'consents' => ['Persetujuan', 'hand-raised'],
        'consent-purposes' => ['Tujuan Persetujuan', 'hand-raised'],
        'consent-records' => ['Catatan Persetujuan', 'clipboard-document-check'],
        'policies' => ['Kebijakan & Versi', 'document-text'],
        'documents' => ['Kebijakan & Versi', 'document-text'],
        'processing-activities' => ['RoPA', 'queue-list'],
        'activities' => ['RoPA', 'queue-list'],
        'ropa' => ['RoPA', 'queue-list'],
        'processors' => ['Pemroses & DPA', 'building-office-2'],
        'vendors' => ['Pemroses & DPA', 'building-office-2'],
        'dpia' => ['DPIA', 'scale'],
        'assessments' => ['DPIA', 'scale'],
        'breaches' => ['Insiden Data', 'exclamation-triangle'],
        'incidents' => ['Insiden Data', 'exclamation-triangle'],
        'retention' => ['Retensi Data', 'clock'],
        'audit' => ['Log Audit', 'clipboard-document-list'],
        'audit-logs' => ['Log Audit', 'clipboard-document-list'],
    ];

    /**
     * Menu items for every parameterless GET admin.privacy.* landing route.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function items(): array
    {
        $found = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = $route->getName();

            if (! $name || ! str_starts_with($name, 'admin.privacy.') || ! self::isLanding($route, $name)) {
                continue;
            }

            $segment = Str::of($name)->after('admin.privacy.')->before('.')->toString();
            $segment = $segment === 'index' ? 'dashboard' : $segment;
            [$label, $icon] = self::KNOWN[$segment] ?? [Str::headline($segment), 'shield-check'];

            // Reason: two route names can map to the same label (e.g. dsr/requests); keep the first.
            $found[$label] ??= [
                'order' => array_search($segment, array_keys(self::KNOWN), true) ?: ($segment === 'dashboard' ? 0 : 999),
                'item' => Menus::item($label, $name, $icon, self::activePatterns($name, $segment)),
            ];
        }

        uasort($found, fn ($a, $b) => $a['order'] <=> $b['order']);

        return array_values(array_map(fn ($entry) => $entry['item'], $found));
    }

    /**
     * A landing route is a GET route without required parameters named *.index,
     * *.dashboard or admin.privacy.<segment>.
     */
    private static function isLanding(RoutingRoute $route, string $name): bool
    {
        if (! in_array('GET', $route->methods(), true) || preg_match('/\{[^}?]+\}/', $route->uri())) {
            return false;
        }

        $rest = Str::after($name, 'admin.privacy.');

        if (! str_contains($rest, '.')) {
            return $rest === 'index' || array_key_exists($rest, self::KNOWN);
        }

        return Str::endsWith($rest, ['.index', '.dashboard']);
    }

    /**
     * routeIs() patterns that keep the item active on its child pages.
     *
     * @return array<int, string>
     */
    private static function activePatterns(string $name, string $segment): array
    {
        return $segment === 'dashboard' ? [$name] : [$name, 'admin.privacy.'.$segment.'.*'];
    }
}
