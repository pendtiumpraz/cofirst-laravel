<?php

namespace Tests\Feature\Auth;

use App\Mail\Privacy\ParentalConsentMail;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Privacy\ConsentRecord;
use App\Models\Privacy\ParentalConsentRequest;
use App\Models\Privacy\PolicyAcceptance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Privacy\Consent\ConsentTestHelpers;
use Tests\TestCase;

/**
 * Self-registration: roles from settings (never staff), policy acknowledgement, optional
 * consents, and the parental-consent path for children (UU PDP Pasal 21-25).
 */
class RegistrationTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Buat akun')
            ->assertSee('Kebijakan Privasi')
            ->assertSee('Persetujuan opsional');
    }

    public function test_adult_student_can_register_with_optional_consent(): void
    {
        $response = $this->post('/register', $this->registrationPayload(['consents' => ['marketing_communications']]));

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'rafi@example.test')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('student'));
        $this->assertSame(now()->subYears(25)->toDateString(), $user->profile->birth_date->toDateString());
        $this->assertSame('0812 3456 7890', $user->profile->phone_number);

        $this->assertSame(['privacy_notice', 'terms'], PolicyAcceptance::where('user_id', $user->id)->where('context', 'registration')->orderBy('policy_type')->pluck('policy_type')->all());
        $this->assertDatabaseHas('consent_records', ['subject_user_id' => $user->id, 'purpose_key' => 'marketing_communications', 'decision' => 'granted', 'method' => 'registration']);
        // Optional consents are unticked by default: nothing recorded for what was not chosen.
        $this->assertDatabaseMissing('consent_records', ['subject_user_id' => $user->id, 'purpose_key' => 'media_publication']);
    }

    public function test_parent_can_register_without_birth_date(): void
    {
        $this->post('/register', $this->registrationPayload([
            'role' => 'parent', 'name' => 'Budi Santoso', 'email' => 'budi@example.test', 'birth_date' => null,
        ]))->assertRedirect(route('dashboard', absolute: false));

        $this->assertTrue(User::where('email', 'budi@example.test')->firstOrFail()->hasRole('parent'));
    }

    public function test_staff_roles_cannot_be_self_registered(): void
    {
        foreach (['teacher', 'admin', 'superadmin', 'finance'] as $role) {
            $this->post('/register', $this->registrationPayload(['role' => $role, 'email' => "x-{$role}@example.test"]))
                ->assertSessionHasErrors('role');
        }

        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_registration_roles_setting_limits_who_can_register(): void
    {
        setting()->set('security.registration_roles', ['parent']);

        $this->get('/register')->assertOk()->assertDontSee('Saya mendaftar sebagai');
        $this->post('/register', $this->registrationPayload())->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_registration_can_be_closed_from_settings(): void
    {
        setting()->set('security.registration_enabled', false);

        $this->get('/register')->assertOk()->assertSee('Pendaftaran sedang ditutup');
        $this->post('/register', $this->registrationPayload())->assertForbidden();
        $this->assertSame(0, User::count());
    }

    public function test_privacy_notice_and_terms_must_be_accepted(): void
    {
        $this->post('/register', $this->registrationPayload(['accept_policies' => null]))
            ->assertSessionHasErrors('accept_policies');

        $this->assertGuest();
    }

    public function test_child_must_name_a_guardian(): void
    {
        $this->post('/register', $this->registrationPayload(['birth_date' => now()->subYears(12)->toDateString()]))
            ->assertSessionHasErrors(['guardian_name', 'guardian_email']);

        $this->assertSame(0, User::count());
    }

    public function test_child_registration_is_pending_until_guardian_consents(): void
    {
        Mail::fake();

        $response = $this->post('/register', $this->registrationPayload([
            'name' => 'Nadia Putri',
            'email' => 'nadia@example.test',
            'birth_date' => now()->subYears(11)->toDateString(),
            'guardian_name' => 'Sari Putri',
            'guardian_email' => 'Sari@Example.test',
            // Reason: a child cannot give optional consents; these must be ignored.
            'consents' => ['media_publication', 'marketing_communications'],
        ]));

        $response->assertRedirect(route('register.success'));
        $this->assertGuest();

        $child = User::where('email', 'nadia@example.test')->firstOrFail();
        $this->assertFalse($child->is_active);
        $this->assertSame(0, ConsentRecord::where('subject_user_id', $child->id)->count());

        $request = ParentalConsentRequest::where('child_user_id', $child->id)->firstOrFail();
        $this->assertSame('pending', $request->status);
        $this->assertSame('sari@example.test', $request->guardian_email);
        $this->assertTrue($request->expires_at->between(now()->addDays(6), now()->addDays(8)));

        Mail::assertSent(ParentalConsentMail::class, fn (ParentalConsentMail $m) => $m->kind === 'request' && $m->hasTo('sari@example.test'));

        $this->followRedirects($response)->assertSee('Satu langkah lagi, Nadia!')->assertSee('s•••@example.test');
    }

    public function test_guardian_email_must_differ_from_the_child_email(): void
    {
        $this->post('/register', $this->registrationPayload([
            'birth_date' => now()->subYears(10)->toDateString(),
            'guardian_name' => 'Sari',
            'guardian_email' => 'rafi@example.test',
        ]))->assertSessionHasErrors('guardian_email');
    }

    public function test_choosing_a_class_reserves_a_pending_seat(): void
    {
        $class = ClassName::factory()->create(['max_students' => 2]);

        $this->post('/register', $this->registrationPayload(['class_id' => $class->id]))->assertRedirect();

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $class->id,
            'student_id' => User::where('email', 'rafi@example.test')->value('id'),
            'status' => Enrollment::STATUS_PENDING,
            'payment_status' => 'unpaid',
        ]);
    }

    public function test_full_class_is_rejected_and_nothing_is_saved(): void
    {
        $class = ClassName::factory()->create(['max_students' => 1]);
        $other = $this->makeUser('student', 20);
        Enrollment::create(['student_id' => $other->id, 'course_id' => $class->course_id, 'class_id' => $class->id, 'enrollment_date' => now(), 'status' => 'pending', 'payment_status' => 'unpaid']);

        $this->post('/register', $this->registrationPayload(['class_id' => $class->id]))->assertSessionHasErrors('class_id');

        $this->assertDatabaseMissing('users', ['email' => 'rafi@example.test']);
        $this->assertGuest();
    }

    public function test_honeypot_blocks_automated_sign_ups(): void
    {
        $this->post('/register', $this->registrationPayload(['website' => 'https://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, User::count());
    }

    public function test_duplicate_email_is_rejected_case_insensitively(): void
    {
        User::factory()->create(['email' => 'rafi@example.test']);

        $this->post('/register', $this->registrationPayload(['email' => 'RAFI@example.test']))
            ->assertSessionHasErrors('email');
    }
}
