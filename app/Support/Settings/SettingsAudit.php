<?php

namespace App\Support\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Audit trail of settings changes. settings.updated_by records the last editor
 * per key; this adds an append-only event (actor, action, keys — never the
 * values, which may contain personal data such as the DPO's phone number).
 *
 * Uses App\Services\Privacy\AuditLogger when the privacy module provides it,
 * otherwise the application log.
 */
final class SettingsAudit
{
    private const LOGGER = 'App\\Services\\Privacy\\AuditLogger';

    /**
     * Record a settings event.
     *
     * Args:
     *     action (string): updated | reset | preview_applied.
     *     group (string): Settings group.
     *     keys (array): Setting keys involved.
     *     actor (User|null): Who did it.
     */
    public static function record(string $action, string $group, array $keys, ?User $actor): void
    {
        if ($keys === []) {
            return;
        }

        $event = 'settings.'.$action;
        $context = ['group' => $group, 'keys' => array_values($keys)];

        if (class_exists(self::LOGGER) && method_exists(self::LOGGER, 'log')) {
            try {
                self::callAuditLogger($event, $context);

                return;
            } catch (Throwable $e) {
                // Reason: auditing must never block saving; fall back to the application log.
                report($e);
            }
        }

        Log::info($event, $context + ['actor_id' => $actor?->getKey()]);
    }

    /**
     * Call AuditLogger::log() whether it takes (action, context) or (action, subject, context).
     *
     * Reason: the privacy module is built in parallel; adapt to its signature instead of guessing one.
     *
     * @param  array<string, mixed>  $context
     */
    private static function callAuditLogger(string $event, array $context): void
    {
        $method = new ReflectionMethod(self::LOGGER, 'log');
        $second = $method->getParameters()[1] ?? null;
        $type = $second?->getType();

        $args = [$event];
        if ($second !== null) {
            if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
                $args[] = $context;
            } else {
                $args[] = null;
                if ($method->getNumberOfParameters() >= 3) {
                    $args[] = $context;
                }
            }
        }

        $method->isStatic()
            ? $method->invokeArgs(null, $args)
            : $method->invokeArgs(app(self::LOGGER), $args);
    }
}
