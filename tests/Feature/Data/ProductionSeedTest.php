<?php

namespace Tests\Feature\Data;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Production seeding: demo accounts (known password) are never created unless
 * opted in, and the first superadmin comes from config('app.seeding'), which
 * survives `php artisan config:cache` (env() does not).
 */
class ProductionSeedTest extends TestCase
{
    use RefreshDatabase;

    private const OWNER_EMAIL = 'pemilik@example.test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->detectEnvironment(fn () => 'production');
        config([
            'app.seeding.demo_data' => false,
            'app.seeding.superadmin' => ['email' => null, 'password' => null, 'name' => null],
        ]);
    }

    public function test_production_seed_creates_the_superadmin_from_config(): void
    {
        config(['app.seeding.superadmin' => ['email' => self::OWNER_EMAIL, 'password' => 'rahasia-kuat-123', 'name' => 'Pemilik']]);

        $this->seedForced(DatabaseSeeder::class);

        $owner = User::where('email', self::OWNER_EMAIL)->firstOrFail();
        $this->assertTrue($owner->hasRole('superadmin'));
        $this->assertSame('Pemilik', $owner->name);
        $this->assertTrue(Hash::check('rahasia-kuat-123', $owner->password));
    }

    public function test_production_seed_without_credentials_creates_no_account(): void
    {
        $this->seedForced(DatabaseSeeder::class);

        $this->assertFalse(User::where('email', self::OWNER_EMAIL)->exists());
    }

    public function test_demo_seeders_refuse_to_run_in_production_unless_enabled(): void
    {
        // Reason: the shared test DB may already hold demo data; free the demo e-mail so
        // a seeder that ran anyway would have to re-create it.
        User::where('email', 'teacher@codingfirst.com')->update(['email' => 'renamed-teacher@example.test']);

        $this->assertFalse(DemoSeeder::allowed());
        $this->seedForced(DemoSeeder::class);
        $this->seedForced(UserSeeder::class);

        $this->assertFalse(User::withTrashed()->where('email', 'teacher@codingfirst.com')->exists());

        config(['app.seeding.demo_data' => true]);
        $this->assertTrue(DemoSeeder::allowed());
    }

    /**
     * Run a seeder the way a production deploy does (db:seed --force).
     *
     * Reason: in production db:seed asks for confirmation unless --force is given.
     */
    private function seedForced(string $class): void
    {
        $this->artisan('db:seed', ['--class' => $class, '--force' => true])->assertExitCode(0);
    }
}
