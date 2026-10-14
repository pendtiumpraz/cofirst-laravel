<?php

namespace Tests\Feature\Smoke;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\TestCase;
use Throwable;

/**
 * Smoke test: hits every parameter-resolvable GET route as each role and
 * fails on any 5xx response. A JSON report per role is written to
 * storage/framework/testing/smoke-{role}.json for inspection.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /** Route names / URI prefixes that must not be smoke-tested. */
    private const SKIP_NAMES = ['logout', 'verification.verify', 'storage.local'];

    private const SKIP_URI_PREFIXES = ['_ignition', 'sanctum', 'storage/', 'livewire', '_debugbar', 'broadcasting'];

    /**
     * Fallback mapping from route parameter name to model class when the
     * controller signature does not type-hint a model.
     */
    private const PARAM_MODELS = [
        'user' => \App\Models\User::class,
        'student' => \App\Models\User::class,
        'child' => \App\Models\User::class,
        'course' => \App\Models\Course::class,
        'class' => \App\Models\ClassName::class,
        'class_id' => \App\Models\ClassName::class,
        'curriculum' => \App\Models\Curriculum::class,
        'syllabus' => \App\Models\Syllabus::class,
        'material' => \App\Models\Material::class,
        'enrollment' => \App\Models\Enrollment::class,
        'schedule' => \App\Models\Schedule::class,
        'report' => \App\Models\Report::class,
        'transaction' => \App\Models\FinancialTransaction::class,
        'certificate' => \App\Models\Certificate::class,
        'badge' => \App\Models\Badge::class,
        'reward' => \App\Models\Reward::class,
        'redemption' => \App\Models\RewardRedemption::class,
        'testimonial' => \App\Models\Testimonial::class,
        'photo' => \App\Models\ClassPhoto::class,
        'class_photo' => \App\Models\ClassPhoto::class,
        'template' => \App\Models\CertificateTemplate::class,
        'certificate_template' => \App\Models\CertificateTemplate::class,
        'conversation' => \App\Models\Conversation::class,
        'message' => \App\Models\Message::class,
        'project_gallery' => \App\Models\ProjectGallery::class,
        'class_report' => \App\Models\ClassReport::class,
        'handover' => \App\Models\TeacherHandover::class,
        'attendance' => \App\Models\Attendance::class,
        'role' => \Spatie\Permission\Models\Role::class,
        'permission' => \Spatie\Permission\Models\Permission::class,
    ];

    /**
     * Roles exercised by the smoke test ("guest" = unauthenticated).
     *
     * @return array<string, array{0: string}>
     */
    public static function roles(): array
    {
        $roles = ['guest', 'superadmin', 'admin', 'teacher', 'student', 'parent', 'finance'];

        return array_combine($roles, array_map(fn ($r) => [$r], $roles));
    }

    /**
     * Ensure demo data exists even when another test class migrated the shared
     * in-memory database first (RefreshDatabase only seeds on the first migration
     * of the process, so a full suite run would otherwise leave it empty).
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (\Spatie\Permission\Models\Role::query()->doesntExist() || User::query()->doesntExist()) {
            $this->seed();
        }
    }

    #[DataProvider('roles')]
    public function test_get_routes_do_not_error(string $role): void
    {
        $user = $role === 'guest' ? null : $this->userForRole($role);

        if ($role !== 'guest' && ! $user) {
            $this->markTestSkipped("No seeded user with role {$role}.");
        }

        $failures = [];
        $report = [];

        foreach ($this->smokeRoutes() as $route) {
            $uri = $this->resolveUri($route, $user, $role);
            $name = $route->getName() ?? $route->uri();

            if ($uri === null) {
                $report[] = ['route' => $name, 'uri' => $route->uri(), 'status' => 'skipped'];
                continue;
            }

            $request = $user
                ? $this->actingAs($user)->withSession(['active_role' => $role])
                : $this;

            $response = $request->get($uri);
            $status = $response->getStatusCode();
            $entry = ['route' => $name, 'uri' => $uri, 'status' => $status];

            if ($status >= 500) {
                $e = $response->exception;
                $entry['error'] = $e
                    ? class_basename($e).': '.$e->getMessage().' @ '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $e->getFile()).':'.$e->getLine()
                    : 'HTTP '.$status;
                $failures[] = "[{$role}] GET /{$uri} ({$name}) => {$entry['error']}";
            }

            $report[] = $entry;

            if ($user) {
                // Reason: reset auth state between requests so a logout-like
                // redirect or session invalidation does not leak into the next one.
                $this->app['auth']->forgetGuards();
            }
        }

        $this->writeReport($role, $report);

        $this->assertSame([], $failures, "Server errors:\n".implode("\n", $failures));
    }

    /**
     * Collect GET routes that are safe to smoke-test.
     *
     * @return array<int, RoutingRoute>
     */
    private function smokeRoutes(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $r) => in_array('GET', $r->methods(), true))
            ->reject(fn (RoutingRoute $r) => in_array($r->getName(), self::SKIP_NAMES, true))
            ->reject(function (RoutingRoute $r) {
                foreach (self::SKIP_URI_PREFIXES as $prefix) {
                    if (str_starts_with($r->uri(), $prefix)) {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }

    /**
     * Pick a representative user for a role, preferring users with related data.
     */
    private function userForRole(string $role): ?User
    {
        $query = User::role($role)->where('is_active', true);

        $preferred = match ($role) {
            'parent' => (clone $query)->whereHas('children')->first(),
            'teacher' => (clone $query)->whereHas('teachingClasses')->first(),
            'student' => (clone $query)->whereHas('enrollments')->first(),
            default => null,
        };

        return $preferred ?? $query->first();
    }

    /**
     * Build a concrete URI for the route, or null when a parameter cannot be resolved.
     */
    private function resolveUri(RoutingRoute $route, ?User $user, string $role): ?string
    {
        $params = [];

        foreach ($route->parameterNames() as $param) {
            $value = $this->resolveParam($route, $param, $user, $role);

            if ($value === null) {
                if ($this->isOptionalParam($route, $param)) {
                    continue;
                }

                return null;
            }

            $params[$param] = $value;
        }

        $uri = $route->uri();

        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{'.preg_quote($key, '/').'\??\}/', rawurlencode((string) $value), $uri);
        }

        // Drop any remaining optional placeholders.
        $uri = preg_replace('/\/?\{[^}]+\?\}/', '', $uri);

        return ltrim($uri, '/');
    }

    private function isOptionalParam(RoutingRoute $route, string $param): bool
    {
        return str_contains($route->uri(), '{'.$param.'?}');
    }

    /**
     * Resolve a route parameter to a route-key value.
     */
    private function resolveParam(RoutingRoute $route, string $param, ?User $user, string $role): string|int|null
    {
        if ($param === 'token') {
            return 'smoke-token';
        }

        if ($param === 'code') {
            $code = class_exists(\App\Models\Certificate::class) && Schema::hasTable('certificates')
                ? \App\Models\Certificate::query()->value('certificate_number')
                : null;

            return $code ?? 'INVALID-CODE';
        }

        $modelClass = $this->modelClassFor($route, $param);

        if (! $modelClass || ! class_exists($modelClass)) {
            return null;
        }

        try {
            $model = $this->pickRecord($modelClass, $param, $user, $role);
        } catch (Throwable) {
            $model = null;
        }

        return $model?->getRouteKey();
    }

    /**
     * Determine the model class for a parameter via controller type-hints, then the fallback map.
     */
    private function modelClassFor(RoutingRoute $route, string $param): ?string
    {
        $action = $route->getActionName();

        if (str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action);

            if (class_exists($controller) && method_exists($controller, $method)) {
                foreach ((new ReflectionMethod($controller, $method))->getParameters() as $p) {
                    $type = $p->getType();

                    if ($p->getName() === $param && $type instanceof ReflectionNamedType && ! $type->isBuiltin()
                        && is_subclass_of($type->getName(), Model::class)) {
                        return $type->getName();
                    }
                }
            }
        }

        return self::PARAM_MODELS[$param] ?? null;
    }

    /**
     * Prefer a record related to the acting user so ownership checks pass.
     */
    private function pickRecord(string $modelClass, string $param, ?User $user, string $role): ?Model
    {
        /** @var Model $instance */
        $instance = new $modelClass;
        $table = $instance->getTable();

        if (! Schema::hasTable($table)) {
            return null;
        }

        if ($modelClass === User::class && $user) {
            if ($role === 'parent' && in_array($param, ['student', 'child'], true)) {
                return $user->children()->first();
            }

            if ($role === 'student' && in_array($param, ['student', 'child', 'user'], true)) {
                return $user;
            }

            if (in_array($param, ['student', 'child'], true)) {
                return User::role('student')->first();
            }
        }

        $query = $modelClass::query();

        if ($user && $role === 'student' && Schema::hasColumn($table, 'student_id')) {
            $owned = (clone $query)->where('student_id', $user->id)->first();
            if ($owned) {
                return $owned;
            }
        }

        if ($user && $role === 'parent' && Schema::hasColumn($table, 'student_id')) {
            $owned = (clone $query)->whereIn('student_id', $user->children()->pluck('users.id'))->first();
            if ($owned) {
                return $owned;
            }
        }

        if ($user && $role === 'teacher' && $table === 'class_names') {
            $owned = $user->teachingClasses()->first();
            if ($owned) {
                return $owned;
            }
        }

        if ($user && $role === 'student' && $table === 'class_names') {
            $owned = $modelClass::whereIn('id', $user->enrollments()->pluck('class_id'))->first();
            if ($owned) {
                return $owned;
            }
        }

        if ($user && Schema::hasColumn($table, 'user_id')) {
            $owned = (clone $query)->where('user_id', $user->id)->first();
            if ($owned) {
                return $owned;
            }
        }

        return $query->first();
    }

    /**
     * Persist the per-role report for later inspection.
     *
     * @param  array<int, array<string, mixed>>  $report
     */
    private function writeReport(string $role, array $report): void
    {
        $dir = storage_path('framework/testing');

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents(
            $dir.DIRECTORY_SEPARATOR."smoke-{$role}.json",
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
