<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function roles(): array
    {
        return [
            'superadmin' => ['superadmin'],
            'admin' => ['admin'],
            'teacher' => ['teacher'],
            'student' => ['student'],
            'parent' => ['parent'],
            'finance' => ['finance'],
        ];
    }

    #[DataProvider('roles')]
    public function test_profile_page_is_displayed_for_every_role(string $role): void
    {
        $user = User::factory()->{$role}()->create();

        $response = $this->actingAs($user)->withSession(['active_role' => $role])->get('/profile');

        $response->assertOk()
            ->assertSee('Profil saya')
            ->assertSee('Informasi pribadi')
            ->assertSee('Foto profil')
            ->assertSee('Ganti kata sandi')
            ->assertSee('Sesi aktif')
            ->assertSee('Keluar dari perangkat lain');
    }

    public function test_delete_account_card_links_to_the_privacy_centre_only_when_it_exists(): void
    {
        $user = User::factory()->parent()->create();

        $response = $this->actingAs($user)->get('/profile')->assertOk();

        if (Route::has('account.privacy.index')) {
            $response->assertSee('id="hapus-akun"', false)->assertSee(route('account.privacy.index'), false);
        } else {
            $response->assertDontSee('id="hapus-akun"', false);
        }

        // The page never offers a direct "delete my account" form (DELETE /profile).
        $this->assertDoesNotMatchRegularExpression(
            '#action="'.preg_quote(route('profile.destroy'), '#').'"[^>]*>\s*<input[^>]*_token[^>]*>\s*<input[^>]*name="_method" value="DELETE"#',
            $response->getContent()
        );
    }

    public function test_delete_account_card_appears_once_the_privacy_centre_exists(): void
    {
        if (! Route::has('account.privacy.index')) {
            Route::middleware('web')->get('/uji/pusat-privasi', fn () => 'ok')->name('account.privacy.index');
            Route::getRoutes()->refreshNameLookups();
        }

        $this->actingAs(User::factory()->student()->create())->get('/profile')
            ->assertOk()
            ->assertSee('id="hapus-akun"', false)
            ->assertSee('Buka Pusat Privasi')
            ->assertSee(route('account.privacy.index'), false)
            ->assertSee('UU PDP Pasal 8');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->parent()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Siti Rahmawati',
            'email' => 'siti@example.test',
            'current_password' => 'password',
            'phone_number' => '0812 3456 7890',
            'birth_date' => '1988-04-12',
            'gender' => 'female',
            'address' => 'Jl. Melati No. 5, Bandung',
            'bio' => 'Ibu dari dua anak.',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Siti Rahmawati', $user->name);
        $this->assertSame('siti@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('0812 3456 7890', $user->profile->phone_number);
        $this->assertSame('1988-04-12', $user->profile->birth_date->format('Y-m-d'));
        $this->assertSame('female', $user->profile->gender);
        $this->assertSame('Siti Rahmawati', $user->profile->full_name);
    }

    public function test_changing_the_email_requires_the_current_password(): void
    {
        $user = User::factory()->parent()->create(['email' => 'lama@example.test']);

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => 'baru@example.test',
        ])->assertSessionHasErrors('current_password')->assertRedirect('/profile');

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => $user->name,
            'email' => 'baru@example.test',
            'current_password' => 'salah-sekali',
        ])->assertSessionHasErrors('current_password');

        $this->assertSame('lama@example.test', $user->refresh()->email);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru',
            'email' => $user->email,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_a_child_student_cannot_change_login_email_or_birth_date(): void
    {
        $student = User::factory()->student()->withProfile(['birth_date' => now()->subYears(11)->format('Y-m-d')])->create([
            'email' => 'anak@example.test',
        ]);
        $originalBirthDate = $student->profile->birth_date->format('Y-m-d');

        $this->actingAs($student)->get('/profile')
            ->assertOk()
            ->assertSee('Email login siswa dikelola oleh orang tua/wali atau admin.');

        $this->actingAs($student)->patch('/profile', [
            'name' => 'Budi Santoso',
            'email' => 'diganti@example.test',
            'birth_date' => '1990-01-01',
            'bio' => 'Suka membuat game.',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $student->refresh();
        $this->assertSame('anak@example.test', $student->email);
        $this->assertSame($originalBirthDate, $student->profile->birth_date->format('Y-m-d'));
        $this->assertSame('Budi Santoso', $student->name);
        $this->assertSame('Suka membuat game.', $student->profile->bio);
    }

    public function test_invalid_profile_values_are_rejected(): void
    {
        $user = User::factory()->parent()->create();

        $this->actingAs($user)->from('/profile')->patch('/profile', [
            'name' => '',
            'email' => $user->email,
            'phone_number' => 'telepon saya',
            'birth_date' => now()->addDay()->format('Y-m-d'),
            'gender' => 'lainnya',
        ])->assertSessionHasErrors(['name', 'phone_number', 'birth_date', 'gender'])->assertRedirect('/profile');
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->parent()->create();

        $response = $this->actingAs($user)->delete('/profile', ['password' => 'password']);

        $response->assertSessionHasNoErrors()->assertRedirect('/');
        $this->assertGuest();

        if (! class_exists('App\\Services\\Privacy\\ErasureService')) {
            // Without the privacy module the account is archived (soft delete) and deactivated.
            $this->assertSoftDeleted($user);
            $this->assertFalse((bool) User::withTrashed()->find($user->id)->is_active);
        }
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->parent()->create();

        $response = $this->actingAs($user)->from('/profile')->delete('/profile', ['password' => 'wrong-password']);

        $response->assertSessionHasErrorsIn('userDeletion', 'password')->assertRedirect('/profile');
        $this->assertNotSoftDeleted($user);
    }

    public function test_staff_accounts_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotSoftDeleted($admin);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_guests_cannot_open_or_update_the_profile(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
        $this->patch('/profile', ['name' => 'X'])->assertRedirect(route('login'));
    }
}
