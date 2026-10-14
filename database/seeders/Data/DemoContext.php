<?php

namespace Database\Seeders\Data;

use App\Models\ClassName;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Shared lookups for the demo seeders: users by blueprint key, classes by code,
 * a seeded Faker and the meeting calendar of each class.
 */
final class DemoContext
{
    /** @var array<string, User>|null */
    private static ?array $users = null;

    /** @var array<string, ClassName>|null */
    private static ?array $classes = null;

    /**
     * A Faker generator (id_ID) seeded for one seeder, so its output is stable.
     *
     * Args:
     *     salt (int): Per-seeder offset so seeders do not share a sequence.
     */
    public static function faker(int $salt = 0): Generator
    {
        $faker = FakerFactory::create('id_ID');
        $faker->seed(DemoBlueprint::FAKER_SEED + $salt);

        return $faker;
    }

    /** Forget cached lookups (call after users/classes were (re)created). */
    public static function flush(): void
    {
        self::$users = null;
        self::$classes = null;
    }

    /**
     * Every demo person: key => e-mail.
     *
     * @return array<string, string>
     */
    public static function emails(): array
    {
        $emails = [];

        foreach (DemoBlueprint::staff() as $key => $person) {
            $emails[$key] = $person['email'];
        }

        foreach (DemoBlueprint::teachers() as $key => $person) {
            $emails[$key] = $person['email'];
        }

        foreach (DemoBlueprint::families() as $key => $family) {
            $emails[$key] = $family['email'];

            foreach ($family['children'] as $childKey => $child) {
                $emails[$childKey] = $child['email'];
            }
        }

        foreach (DemoBlueprint::adults() as $key => $person) {
            $emails[$key] = $person['email'];
        }

        return $emails;
    }

    /** The user for a blueprint key. */
    public static function user(string $key): User
    {
        return self::users()[$key] ?? throw new \RuntimeException("Demo user [{$key}] not seeded.");
    }

    /**
     * All demo users keyed by blueprint key.
     *
     * @return array<string, User>
     */
    public static function users(): array
    {
        if (self::$users === null) {
            $emails = self::emails();
            $byEmail = User::whereIn('email', array_values($emails))->get()->keyBy('email');
            self::$users = [];

            foreach ($emails as $key => $email) {
                if ($byEmail->has($email)) {
                    self::$users[$key] = $byEmail[$email];
                }
            }
        }

        return self::$users;
    }

    /**
     * Keys of all demo students (children first, then adults).
     *
     * @return array<int, string>
     */
    public static function studentKeys(): array
    {
        $keys = [];

        foreach (DemoBlueprint::families() as $family) {
            array_push($keys, ...array_keys($family['children']));
        }

        return array_merge($keys, array_keys(DemoBlueprint::adults()));
    }

    /**
     * Parent key of a child (null for adults).
     */
    public static function parentKeyOf(string $studentKey): ?string
    {
        foreach (DemoBlueprint::families() as $parentKey => $family) {
            if (isset($family['children'][$studentKey])) {
                return $parentKey;
            }
        }

        return null;
    }

    /** The class for a blueprint code. */
    public static function classByCode(string $code): ClassName
    {
        return self::classes()[$code] ?? throw new \RuntimeException("Demo class [{$code}] not seeded.");
    }

    /**
     * All demo classes keyed by code.
     *
     * @return array<string, ClassName>
     */
    public static function classes(): array
    {
        if (self::$classes === null) {
            $plan = DemoBlueprint::classes();
            $names = array_combine(array_column($plan, 'name'), array_keys($plan));
            self::$classes = [];

            foreach (ClassName::whereIn('name', array_keys($names))->with(['course', 'curriculum'])->get() as $class) {
                self::$classes[$names[$class->name]] = $class;
            }
        }

        return self::$classes;
    }

    /** Ids of all demo classes. */
    public static function classIds(): array
    {
        return array_values(array_map(fn (ClassName $c) => $c->id, self::classes()));
    }

    /** Ids of all demo students. */
    public static function studentIds(): array
    {
        return array_values(array_map(fn (string $key) => self::user($key)->id, self::studentKeys()));
    }

    /**
     * Date of the first meeting for a class plan.
     *
     * Args:
     *     plan (array): Entry of DemoBlueprint::classes().
     */
    public static function firstMeeting(array $plan): Carbon
    {
        return Carbon::today()->startOfWeek(Carbon::MONDAY)
            ->subWeeks($plan['start_weeks'])
            ->addDays($plan['day'] - 1);
    }

    /**
     * Dates of every meeting of a class plan (weekly).
     *
     * @return Collection<int, Carbon> meeting number => date
     */
    public static function meetingDates(array $plan): Collection
    {
        $first = self::firstMeeting($plan);

        return collect(range(1, $plan['meetings']))
            ->mapWithKeys(fn (int $n) => [$n => $first->copy()->addWeeks($n - 1)]);
    }

    /**
     * Meetings that already took place (date before today).
     *
     * @return Collection<int, Carbon>
     */
    public static function pastMeetings(array $plan): Collection
    {
        return self::meetingDates($plan)->filter(fn (Carbon $date) => $date->lt(Carbon::today()));
    }

    /**
     * Deterministic pseudo-random integer for a string key (stable across runs).
     */
    public static function pick(string $key, int $modulo): int
    {
        return $modulo <= 1 ? 0 : (int) (crc32($key) % $modulo);
    }
}
