<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG = 'Sandi-Baru-2026';

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertOk()->assertSee('Lupa kata sandi?')->assertSee('Kirim tautan');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->student()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', fn ($status) => str_starts_with($status, PasswordResetLinkController::NEUTRAL_STATUS));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_emails_get_the_same_neutral_answer(): void
    {
        Notification::fake();

        $this->from('/forgot-password')->post('/forgot-password', ['email' => 'tidak-ada@example.test'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', fn ($status) => str_starts_with($status, PasswordResetLinkController::NEUTRAL_STATUS));

        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->student()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $this->get('/reset-password/'.$notification->token)
                ->assertOk()
                ->assertSee('Buat kata sandi baru')
                ->assertSee('Huruf besar dan huruf kecil');

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->student()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => self::STRONG,
                'password_confirmation' => self::STRONG,
            ]);

            $response->assertSessionHasNoErrors()->assertRedirect(route('login'))->assertSessionHas('status');
            $this->assertTrue(Hash::check(self::STRONG, $user->refresh()->password));

            return true;
        });
    }

    public function test_weak_passwords_are_rejected_on_reset(): void
    {
        Notification::fake();

        $user = User::factory()->student()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])->assertSessionHasErrors('password');

            return true;
        });
    }

    public function test_an_invalid_token_does_not_reveal_whether_the_email_exists(): void
    {
        $user = User::factory()->student()->create();

        $known = $this->from('/reset-password/salah')->post('/reset-password', [
            'token' => 'salah', 'email' => $user->email,
            'password' => self::STRONG, 'password_confirmation' => self::STRONG,
        ])->assertSessionHasErrors('email');
        $knownMessage = session('errors')->first('email');

        $this->from('/reset-password/salah')->post('/reset-password', [
            'token' => 'salah', 'email' => 'tidak-ada@example.test',
            'password' => self::STRONG, 'password_confirmation' => self::STRONG,
        ])->assertSessionHasErrors('email');

        $this->assertSame($knownMessage, session('errors')->first('email'));
    }
}
