<?php

namespace Database\Seeders;

use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * The complete, mutually consistent demo dataset (fictional people, id_ID).
 *
 * Deterministic (fixed Faker seed, explicit blueprint) and idempotent (natural
 * keys / rebuild of date-relative rows), so it is safe to run again on an
 * existing database. Never runs in production unless SEED_DEMO_DATA=true.
 */
class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds. Order matters: later seeders read earlier data.
     */
    public function run(): void
    {
        // Reason: the demo accounts share the password "password"; refuse in production
        // even when called directly (db:seed --class=DemoSeeder) unless opted in.
        if (! self::allowed()) {
            $this->command?->warn('DemoSeeder skipped: production. Set SEED_DEMO_DATA=true to seed demo data there.');

            return;
        }

        // Reason: Faker and any rand()/array_rand() share PHP's Mersenne Twister.
        mt_srand(DemoBlueprint::FAKER_SEED);
        DemoContext::flush();

        $this->call([
            UserSeeder::class,
            ClassNameSeeder::class,
            TeacherAssignmentSeeder::class,
            EnrollmentSeeder::class,
            ScheduleSeeder::class,
            LearningActivitySeeder::class,
            ReportSeeder::class,
            FinancialTransactionSeeder::class,
            AnnouncementSeeder::class,
            GamificationDemoSeeder::class,
            CertificateSeeder::class,
            ChatSeeder::class,
            TestimonialSeeder::class,
            ProjectGallerySeeder::class,
        ]);

        DemoContext::flush();
    }

    /**
     * Whether demo data may be seeded: everywhere except production, where it is
     * opt-in (SEED_DEMO_DATA=true, read through config so config:cache keeps it).
     *
     * Returns:
     *     bool: True when demo data may be seeded.
     */
    public static function allowed(): bool
    {
        return ! app()->environment('production') || (bool) config('app.seeding.demo_data', false);
    }
}
