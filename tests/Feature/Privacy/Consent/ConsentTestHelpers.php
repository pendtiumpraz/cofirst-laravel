<?php

namespace Tests\Feature\Privacy\Consent;

use App\Mail\Privacy\ParentalConsentMail;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\PrivacySeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Mail;

/**
 * Shared fixtures for the PDP consent tests: roles + purposes + policy v1.0,
 * users with a given age, and a child registration that captures the guardian link.
 */
trait ConsentTestHelpers
{
    /** Seed roles, the purpose register and version 1.0 of every policy. */
    protected function seedPrivacy(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(PrivacySeeder::class);
    }

    /**
     * An active user with a role and (optionally) an age.
     */
    protected function makeUser(string $role, ?int $age = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['is_active' => true]);
        $user->assignRole($role);

        Profile::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'birth_date' => $age !== null ? now()->subYears($age)->subDays(10)->toDateString() : null,
        ]);

        return $user->fresh('profile');
    }

    /**
     * Valid registration payload; override any field.
     *
     * @return array<string, mixed>
     */
    protected function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'role' => 'student',
            'name' => 'Rafi Pratama',
            'email' => 'rafi@example.test',
            'phone' => '0812 3456 7890',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'birth_date' => now()->subYears(25)->toDateString(),
            'accept_policies' => '1',
        ], $overrides);
    }

    /**
     * Register a child and return [child, signed guardian URL].
     *
     * @return array{0: User, 1: string}
     */
    protected function registerChild(array $overrides = []): array
    {
        Mail::fake();

        $this->post('/register', $this->registrationPayload(array_merge([
            'name' => 'Nadia Putri',
            'email' => 'nadia@example.test',
            'birth_date' => now()->subYears(11)->toDateString(),
            'guardian_name' => 'Sari Putri',
            'guardian_email' => 'sari@example.test',
            'guardian_relationship' => 'ibu',
        ], $overrides)))->assertRedirect(route('register.success'));

        $url = null;
        Mail::assertSent(ParentalConsentMail::class, function (ParentalConsentMail $mail) use (&$url) {
            if ($mail->kind === 'request') {
                $url = $mail->url;
            }

            return $mail->kind === 'request';
        });

        $child = User::query()->where('email', $overrides['email'] ?? 'nadia@example.test')->firstOrFail();

        return [$child, $url];
    }

    /** Path + query of an absolute URL (for $this->get()). */
    protected function relative(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
