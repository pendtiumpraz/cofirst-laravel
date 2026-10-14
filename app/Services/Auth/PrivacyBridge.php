<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Optional hooks into the UU PDP modules (audit log, account erasure).
 *
 * Those services are built by the privacy engineers; this bridge calls them only
 * when they exist, so authentication and profile keep working on their own.
 */
final class PrivacyBridge
{
    private const AUDIT_LOGGER = 'App\\Services\\Privacy\\AuditLogger';

    private const ERASURE_SERVICE = 'App\\Services\\Privacy\\ErasureService';

    /** Method names an erasure service may expose, in order of preference. */
    private const ERASURE_METHODS = ['erase', 'eraseUser', 'anonymize', 'anonymise', 'handle', 'execute'];

    /**
     * Record a security or privacy event in the audit log (no-op without AuditLogger).
     *
     * Context must never hold request payloads with personal data — ids and counts only.
     *
     * Args:
     *     action (string): Event key, e.g. "account.sessions.revoked".
     *     subject (Model|null): The record the action was about.
     *     context (array): Extra non-personal details.
     */
    public static function audit(string $action, ?Model $subject = null, array $context = []): void
    {
        if (! class_exists(self::AUDIT_LOGGER) || ! method_exists(self::AUDIT_LOGGER, 'log')) {
            return;
        }

        try {
            $method = new ReflectionMethod(self::AUDIT_LOGGER, 'log');
            $arguments = self::mapArguments($method, $action, $subject, $context);
            $target = $method->isStatic() ? null : app(self::AUDIT_LOGGER);

            $method->invokeArgs($target, $arguments);
        } catch (Throwable $e) {
            // Reason: an audit-log failure must never block the user's own action.
            report($e);
        }
    }

    /**
     * Whether the privacy module's erasure service is installed.
     */
    public static function erasureAvailable(): bool
    {
        return class_exists(self::ERASURE_SERVICE);
    }

    /**
     * Erase/anonymise an account through the privacy module.
     *
     * Returns:
     *     bool: True when the service handled the request, false when it is missing
     *           or exposes no compatible method.
     *
     * Raises:
     *     Throwable: Any error thrown by the erasure service itself.
     */
    public static function erase(User $user): bool
    {
        if (! self::erasureAvailable()) {
            return false;
        }

        $service = app(self::ERASURE_SERVICE);

        foreach (self::ERASURE_METHODS as $name) {
            if (method_exists($service, $name)) {
                $service->{$name}($user);

                return true;
            }
        }

        return false;
    }

    /**
     * Build the argument list for AuditLogger::log by parameter name, so the bridge
     * works whatever order the logger declares (action, subject, context).
     *
     * @return array<int, mixed>
     */
    private static function mapArguments(ReflectionMethod $method, string $action, ?Model $subject, array $context): array
    {
        $arguments = [];

        foreach ($method->getParameters() as $index => $parameter) {
            $name = strtolower($parameter->getName());
            $type = $parameter->getType();
            $wantsArray = $type instanceof ReflectionNamedType && $type->getName() === 'array';

            $value = match (true) {
                str_contains($name, 'action'), str_contains($name, 'event'), $name === 'name', $name === 'type' => $action,
                $wantsArray, str_contains($name, 'context'), str_contains($name, 'meta'), str_contains($name, 'propert'), str_contains($name, 'detail'), str_contains($name, 'data') => $context,
                str_contains($name, 'subject'), str_contains($name, 'model'), str_contains($name, 'target'), str_contains($name, 'auditable') => $subject,
                $index === 0 => $action,
                $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                default => null,
            };

            $arguments[] = $value;
        }

        return $arguments;
    }
}
