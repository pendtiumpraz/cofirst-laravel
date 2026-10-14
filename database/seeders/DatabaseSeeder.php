<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Entry point of `php artisan db:seed`.
 *
 * 1. Base data, safe in every environment (never overwrites admin edits):
 *    roles & permissions, course catalogue, curricula, badge/reward catalogue,
 *    default certificate template.
 * 2. Demo data (DemoSeeder) outside production, or when SEED_DEMO_DATA=true.
 * 3. In production only: a superadmin from SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD
 *    when both are set (no account with a known password is ever created there).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CourseSeeder::class,
            EnhancedCurriculumSeeder::class,
            GamificationSeeder::class,
            CertificateTemplateSeeder::class,
            PrivacySeeder::class,
        ]);

        if (DemoSeeder::allowed()) {
            $this->call(DemoSeeder::class);

            return;
        }

        $this->seedProductionSuperadmin();
    }

    /**
     * Create the production superadmin from environment variables, if given.
     *
     * Reason: read through config('app.seeding') because env() returns null for
     * .env values once the configuration is cached (php artisan config:cache).
     */
    private function seedProductionSuperadmin(): void
    {
        $email = config('app.seeding.superadmin.email');
        $password = config('app.seeding.superadmin.password');

        if (! $email || ! $password) {
            $this->command?->warn('Production: set SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD to create the first superadmin.');

            return;
        }

        $user = User::withTrashed()->firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->forceFill([
                'name' => config('app.seeding.superadmin.name') ?: 'Super Admin',
                'password' => Hash::make($password),
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole('superadmin');
        $this->command?->info("Production superadmin ready: {$email}");
    }
}
