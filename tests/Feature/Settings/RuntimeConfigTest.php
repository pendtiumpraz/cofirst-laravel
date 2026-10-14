<?php

namespace Tests\Feature\Settings;

use App\Support\Settings\RuntimeConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

/**
 * SettingsServiceProvider applies saved settings to the framework config.
 */
class RuntimeConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Reason: date_default_timezone_set() is process-wide; keep later tests on the default.
        date_default_timezone_set('Asia/Jakarta');

        parent::tearDown();
    }

    public function test_defaults_are_applied_at_boot(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('Coding First', config('app.name'));
        $this->assertSame('no-reply@codingfirst.id', config('mail.from.address'));
        $this->assertSame(120, config('session.lifetime'));
        $this->assertSame('Okt', Carbon::create(2026, 10, 13)->translatedFormat('M'));
    }

    public function test_saved_settings_change_the_runtime_config(): void
    {
        setting()->setMany([
            'general.site_name' => 'Sekolah Coding',
            'general.timezone' => 'Asia/Makassar',
            'general.locale' => 'en',
            'notifications.from_address' => 'halo@sekolah.test',
            'notifications.from_name' => 'Sekolah Coding',
            'security.session_lifetime_minutes' => 30,
        ]);

        RuntimeConfig::apply($this->app, setting());

        $this->assertSame('Sekolah Coding', config('app.name'));
        $this->assertSame('Asia/Makassar', config('app.timezone'));
        $this->assertSame('Asia/Makassar', date_default_timezone_get());
        $this->assertSame('en', app()->getLocale());
        $this->assertSame('halo@sekolah.test', config('mail.from.address'));
        $this->assertSame('Sekolah Coding', config('mail.from.name'));
        $this->assertSame(30, config('session.lifetime'));
    }

    public function test_invalid_stored_values_are_ignored(): void
    {
        setting()->setMany([
            'general.timezone' => 'Mars/Olympus',
            'general.locale' => 'xx',
            'notifications.from_address' => 'bukan-email',
            'security.session_lifetime_minutes' => 0,
        ]);

        RuntimeConfig::apply($this->app, setting());

        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('no-reply@codingfirst.id', config('mail.from.address'));
        $this->assertSame(120, config('session.lifetime'));
    }

    public function test_password_policy_follows_the_security_settings(): void
    {
        $passes = fn (string $password) => Validator::make(['password' => $password], ['password' => Password::defaults()])->passes();

        $this->assertFalse($passes('password'), 'Default policy needs mixed case and numbers.');
        $this->assertTrue($passes('Belajar2026'));

        setting()->setMany([
            'security.password_min_length' => 12,
            'security.password_require_symbols' => true,
        ]);

        $this->assertFalse($passes('Belajar2026'), 'Too short and no symbol.');
        $this->assertTrue($passes('Belajar-2026!'));
    }

    public function test_defaults_apply_when_the_settings_table_is_missing(): void
    {
        Schema::drop('settings');
        setting()->flush();

        $this->assertSame('Coding First', setting('general.site_name'));

        RuntimeConfig::apply($this->app, setting());
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }
}
