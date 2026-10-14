<?php

namespace App\Support\Settings;

use App\Models\User;

/**
 * Per-user preferences stored in users.preferences (JSON).
 *
 * Keys (shared contract with App\Support\Navigation\Appearance and the
 * notification senders):
 *   theme          a|b            only honoured while appearance.allow_user_theme is on
 *   color_mode     light|dark|system  only honoured while appearance.allow_user_color_mode is on
 *   density        comfortable|compact
 *   text_size      sm|md|lg
 *   reduce_motion  bool
 *   locale         id|en
 *   notifications.email.{report|payment_reminder|chat_message}  bool (default true)
 *
 * Senders must call UserPreferences::wantsEmail($user, 'report') before emailing.
 */
final class UserPreferences
{
    public const COLOR_MODES = ['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Ikuti perangkat'];

    public const LOCALES = ['id' => 'Bahasa Indonesia', 'en' => 'English'];

    public const DENSITIES = ['comfortable' => 'Lega', 'compact' => 'Rapat'];

    public const TEXT_SIZES = ['sm' => 'Kecil', 'md' => 'Normal', 'lg' => 'Besar'];

    /**
     * Email notification types a user can switch off.
     * roles: who receives this kind of email (null = everyone); setting: global switch in Pengaturan.
     */
    public const EMAIL_NOTIFICATIONS = [
        'report' => [
            'label' => 'Laporan pertemuan baru',
            'description' => 'Email saat pengajar menerbitkan laporan perkembangan.',
            'roles' => ['parent', 'student'],
            'setting' => 'notifications.report_published',
        ],
        'payment_reminder' => [
            'label' => 'Pengingat pembayaran',
            'description' => 'Email sebelum dan saat tagihan jatuh tempo.',
            'roles' => ['parent', 'student'],
            'setting' => 'notifications.payment_reminder',
        ],
        'chat_message' => [
            'label' => 'Pesan chat baru',
            'description' => 'Email bila ada pesan yang belum dibaca selama beberapa saat.',
            'roles' => null,
            'setting' => null,
        ],
    ];

    /**
     * Email notification types relevant to the user, with their current state.
     *
     * @return array<string, array{label:string, description:string, enabled:bool, available:bool}>
     */
    public static function emailOptionsFor(User $user): array
    {
        $options = [];

        foreach (self::EMAIL_NOTIFICATIONS as $type => $meta) {
            if ($meta['roles'] !== null && ! $user->hasAnyRole($meta['roles'])) {
                continue;
            }

            $options[$type] = [
                'label' => $meta['label'],
                'description' => $meta['description'],
                'enabled' => self::emailPreference($user, $type),
                // Reason: when the owner switched the notification off for everyone, the user toggle is moot.
                'available' => $meta['setting'] === null || setting()->enabled($meta['setting']),
            ];
        }

        return $options;
    }

    /**
     * Whether the user should receive an email of the given type (global switch AND personal choice).
     *
     * Args:
     *     user (User): Recipient.
     *     type (string): report | payment_reminder | chat_message.
     *
     * Returns:
     *     bool: True when the email may be sent.
     */
    public static function wantsEmail(User $user, string $type): bool
    {
        $meta = self::EMAIL_NOTIFICATIONS[$type] ?? null;

        if ($meta === null) {
            return true;
        }

        if ($meta['setting'] !== null && ! setting()->enabled($meta['setting'])) {
            return false;
        }

        return self::emailPreference($user, $type);
    }

    /**
     * The user's personal choice for an email type (default: on).
     */
    public static function emailPreference(User $user, string $type): bool
    {
        return filter_var($user->preference("notifications.email.{$type}", true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * The user's preferred locale, or null when they follow the site default.
     */
    public static function locale(?User $user): ?string
    {
        $locale = $user?->preference('locale');

        return is_string($locale) && array_key_exists($locale, self::LOCALES) ? $locale : null;
    }
}
