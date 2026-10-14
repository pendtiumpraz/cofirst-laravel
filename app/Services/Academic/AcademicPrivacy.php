<?php

namespace App\Services\Academic;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;

/**
 * Bridge from the academic module to the privacy (UU PDP) module.
 *
 * The privacy services are built by another module; every call is guarded with
 * class_exists so the academic portals keep working before (or without) them.
 */
class AcademicPrivacy
{
    /** Privacy service classes (optional). */
    public const AUDIT_LOGGER = 'App\\Services\\Privacy\\AuditLogger';

    public const CONSENT_SERVICE = 'App\\Services\\Privacy\\ConsentService';

    /**
     * Consent purposes that cover documentation photos/videos of a child, in order of
     * preference; the first purpose that exists in the consent module decides.
     *
     * Reason: there is no dedicated "class documentation" purpose yet, so the parental
     * consent for processing the child's learning data (UU PDP Pasal 25) is used; a
     * more specific purpose takes over automatically once the privacy team adds it.
     */
    public const MEDIA_PURPOSES = ['class_documentation', 'learning_media', 'child_data_processing'];

    /**
     * Write an audit-log entry (exports, bulk views of personal data, destructive actions).
     *
     * Args:
     *     action (string): Event key, e.g. "academic.report.export".
     *     subject (Model|null): The affected record.
     *     context (array): Extra non-sensitive details (ids, counts, filters).
     */
    public static function audit(string $action, ?Model $subject = null, array $context = []): void
    {
        if (! class_exists(self::AUDIT_LOGGER) || ! method_exists(self::AUDIT_LOGGER, 'log')) {
            return;
        }

        try {
            // Reason: the logger is written by another engineer; map our values onto its
            // parameters by name so a different (but conventional) signature still works.
            $method = new ReflectionMethod(self::AUDIT_LOGGER, 'log');
            $args = [];

            foreach ($method->getParameters() as $parameter) {
                $name = strtolower($parameter->getName());
                $value = match (true) {
                    in_array($name, ['action', 'event', 'type', 'name', 'key'], true) => $action,
                    in_array($name, ['subject', 'model', 'target', 'auditable', 'record'], true) => $subject,
                    in_array($name, ['properties', 'context', 'meta', 'metadata', 'data', 'details', 'payload', 'extra'], true) => $context,
                    in_array($name, ['description', 'message', 'summary'], true) => $action,
                    in_array($name, ['actor', 'user', 'causer'], true) => auth()->user(),
                    $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                    $parameter->allowsNull() => null,
                    default => throw new \InvalidArgumentException('Unknown AuditLogger parameter '.$name),
                };
                $args[] = $value;
            }

            $method->isStatic()
                ? $method->invokeArgs(null, $args)
                : $method->invokeArgs(app(self::AUDIT_LOGGER), $args);
        } catch (\Throwable $e) {
            // Never break the page because auditing failed; the failure itself is reported.
            report($e);
        }
    }

    /**
     * Whether the student's parent/guardian consented to documentation photos/videos.
     *
     * Adults (known birth date at or above the child-age threshold) decide for
     * themselves, so no parental consent is checked for them. Students without a birth
     * date are treated as children (privacy by default).
     *
     * Returns:
     *     bool|null: null when not applicable or the consent module is not installed.
     */
    public static function mediaConsent(User $student): ?bool
    {
        if (! class_exists(self::CONSENT_SERVICE) || ! method_exists(self::CONSENT_SERVICE, 'hasConsent')) {
            return null;
        }

        try {
            $birthDate = $student->profile?->birth_date;

            if ($birthDate && ! $student->isChild()) {
                return null;
            }

            $service = app(self::CONSENT_SERVICE);

            foreach (self::MEDIA_PURPOSES as $purpose) {
                if (method_exists($service, 'purpose') && ! $service->purpose($purpose)) {
                    continue;
                }

                return (bool) $service->hasConsent($student, $purpose);
            }

            return null;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
