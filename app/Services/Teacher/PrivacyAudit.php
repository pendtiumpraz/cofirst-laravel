<?php

namespace App\Services\Teacher;

use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;
use Throwable;

/**
 * Thin bridge from the teacher portal to the UU PDP audit log
 * (App\Services\Privacy\AuditLogger, built by the privacy governance module).
 *
 * The teacher portal records bulk views and exports of students' personal data
 * (rosters, attendance history, CSV export) and handover decisions. When the
 * governance module is not installed the call is a no-op; a failing audit write
 * never breaks the teacher's request (it is reported instead).
 */
class PrivacyAudit
{
    private const LOGGER = 'App\\Services\\Privacy\\AuditLogger';

    /**
     * Record one audit event.
     *
     * Args:
     *     action (string): Event key, e.g. "teacher.roster.viewed".
     *     subject (Model|null): The record the event is about (class, handover…).
     *     meta (array): Non-sensitive context (ids, counts, filters) — never names or emails.
     */
    public static function log(string $action, ?Model $subject = null, array $meta = []): void
    {
        $logger = self::LOGGER;

        if (! class_exists($logger) || ! method_exists($logger, 'log')) {
            return;
        }

        try {
            $method = new ReflectionMethod($logger, 'log');
            // Reason: the governance module's signature is not frozen yet, so arguments
            // are matched by parameter name instead of position.
            $args = [];
            foreach ($method->getParameters() as $param) {
                $name = strtolower($param->getName());
                $value = match (true) {
                    in_array($name, ['action', 'event', 'type', 'name', 'key'], true) => $action,
                    in_array($name, ['subject', 'model', 'target', 'auditable', 'record', 'entity'], true) => $subject,
                    in_array($name, ['meta', 'metadata', 'context', 'properties', 'data', 'details', 'payload', 'extra', 'attributes'], true) => $meta,
                    in_array($name, ['description', 'message', 'summary'], true) => $meta['description'] ?? $action,
                    in_array($name, ['actor', 'user', 'causer'], true) => auth()->user(),
                    $param->isDefaultValueAvailable() => $param->getDefaultValue(),
                    default => null,
                };
                $args[$param->getName()] = $value;
            }

            $method->isStatic() ? $logger::log(...$args) : app($logger)->log(...$args);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
