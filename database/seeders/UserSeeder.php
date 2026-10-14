<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo accounts: 1 superadmin, 1 admin, 1 finance, 8 teachers, 12 parents,
 * 16 children (ages 8-17, linked to their parents) and 8 adult students.
 *
 * The demo logins superadmin@, admin@, finance@, teacher@, student@ and
 * parent@codingfirst.com (password "password") are kept; student@ is a child of
 * parent@. Re-running updates the same rows (keyed by e-mail).
 */
class UserSeeder extends Seeder
{
    private string $passwordHash;

    private Generator $faker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reason: these accounts share a known password; never create them in
        // production unless demo data was explicitly enabled (see DemoSeeder::allowed()).
        if (! DemoSeeder::allowed()) {
            $this->command?->warn('UserSeeder skipped: production. Set SEED_DEMO_DATA=true to seed demo accounts there.');

            return;
        }

        // Reason: hashing once keeps the seeder fast (bcrypt is deliberately slow).
        $this->passwordHash = Hash::make(DemoBlueprint::PASSWORD);
        $this->faker = DemoContext::faker(1);

        foreach (DemoBlueprint::staff() as $person) {
            $this->account($person, $person['role'], $person['bio']);
        }

        foreach (DemoBlueprint::teachers() as $person) {
            $this->account($person, 'teacher', $person['bio']);
        }

        // Reason: create the demo parent and the demo student before the other
        // parents/students so they are the first user of their role.
        $families = DemoBlueprint::families();
        $parents = [];
        $children = [];

        foreach ($families as $parentKey => $family) {
            $parents[$parentKey] = $this->account($family, 'parent', 'Orang tua siswa Coding First.');

            if ($parentKey === array_key_first($families)) {
                foreach ($family['children'] as $childKey => $child) {
                    $children[$childKey] = $this->account($child, 'student', null);
                }
            }
        }

        foreach ($families as $parentKey => $family) {
            foreach ($family['children'] as $childKey => $child) {
                $children[$childKey] ??= $this->account($child, 'student', null);
                $this->linkParent($parents[$parentKey], $children[$childKey]);
            }
        }

        foreach (DemoBlueprint::adults() as $person) {
            $this->account($person, 'student', 'Peserta kelas dewasa.');
        }

        DemoContext::flush();
        $this->command?->info('Demo users: '.count(DemoContext::emails()).' accounts, '.count($children).' children linked to parents.');
    }

    /**
     * Create or update one account with its role and profile.
     *
     * Args:
     *     person (array): name, email, gender, age.
     *     role (string): Role name.
     *     bio (string|null): Profile bio.
     *
     * Returns:
     *     User: The account.
     */
    private function account(array $person, string $role, ?string $bio): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $person['email']]);
        // Reason: forceFill because email_verified_at/deleted_at are not mass assignable;
        // a previously archived demo account is restored.
        $user->forceFill([
            'name' => $person['name'],
            'password' => $this->passwordHash,
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? Carbon::now()->subMonths(6),
            'deleted_at' => null,
        ])->save();

        $user->syncRoles([$role]);

        // Reason: a stable birthday per person (age from the blueprint, day from the e-mail).
        $birthDate = Carbon::today()->subYears($person['age'])
            ->subDays(DemoContext::pick($person['email'], 300) + 1);

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $person['name'],
                'phone_number' => $this->faker->numerify('08##########'),
                'address' => $this->faker->streetAddress().', '.$this->faker->city(),
                'birth_date' => $birthDate->toDateString(),
                'gender' => $person['gender'],
                'bio' => $bio,
                'avatar' => null,
            ]
        );

        return $user;
    }

    /**
     * Link a parent and a child once (parent_student pivot).
     */
    private function linkParent(User $parent, User $child): void
    {
        DB::table('parent_student')->updateOrInsert(
            ['parent_id' => $parent->id, 'student_id' => $child->id],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }
}
