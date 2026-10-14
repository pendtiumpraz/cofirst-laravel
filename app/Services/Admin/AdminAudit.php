<?php

namespace App\Services\Admin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin, failure-safe bridge to the privacy module's audit log (UU PDP Pasal 31:
 * record of processing; access to personal data must be traceable).
 *
 * Reason: App\Services\Privacy\AuditLogger is built by the governance module and
 * may not exist yet (or may change signature); admin actions must never fail
 * because auditing is unavailable. Context must not contain personal data
 * values (no names, emails or phone numbers) — ids and field names only.
 */
final class AdminAudit
{
    private const LOGGER = 'App\\Services\\Privacy\\AuditLogger';

    /**
     * Record an admin action.
     *
     * Args:
     *     action (string): Dotted action key, e.g. "user.archived".
     *     subject (Model|null): The record acted upon.
     *     context (array): Extra non-personal details (ids, changed field names, counts).
     */
    public static function log(string $action, ?Model $subject = null, array $context = []): void
    {
        if (! class_exists(self::LOGGER)) {
            return;
        }

        try {
            call_user_func([self::LOGGER, 'log'], $action, $subject, $context);
        } catch (Throwable $e) {
            // Reason: log only the failure type, never the payload (it may describe a person).
            Log::warning('Audit log tidak tersimpan', ['action' => $action, 'error' => class_basename($e)]);
        }
    }
}
