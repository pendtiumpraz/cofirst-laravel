<?php

namespace Tests\Feature\Privacy\Consent;

use App\Mail\Privacy\ParentalConsentMail;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Privacy\ConsentRecord;
use App\Models\Privacy\ParentalConsentRequest;
use App\Models\Privacy\PolicyAcceptance;
use App\Models\User;
use App\Services\Privacy\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Child registration → pending → guardian approves / declines via the signed link
 * (UU PDP Pasal 25 ayat (2); PP 33/2026 Pasal 38).
 */
class ParentalConsentFlowTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
    }

    /** @return array<string, mixed> */
    private function grantPayload(array $overrides = []): array
    {
        return array_merge([
            'decision' => 'grant',
            'guardian_name' => 'Sari Putri',
            'guardian_declaration' => '1',
            'required_consent' => '1',
            'consents' => ['media_publication'],
        ], $overrides);
    }

    public function test_guardian_approves_and_child_becomes_active(): void
    {
        [$child, $url] = $this->registerChild();

        $this->get($url)->assertOk()->assertSee('Nadia ingin bergabung')->assertSee('Persetujuan opsional');

        $this->post($url, $this->grantPayload())->assertRedirect($url);

        $child->refresh();
        $this->assertTrue($child->is_active);

        $record = ConsentRecord::where('subject_user_id', $child->id)->where('purpose_key', 'child_data_processing')->firstOrFail();
        $this->assertSame('granted', $record->decision);
        $this->assertSame('parental', $record->method);
        $this->assertSame('guardian', $record->actor_role);
        $this->assertSame('sari@example.test', $record->actor_email);
        $this->assertTrue($record->evidence['guardian_declaration']);

        $consents = app(ConsentService::class);
        $this->assertTrue($consents->hasConsent($child, 'media_publication'));
        $this->assertFalse($consents->hasConsent($child, 'leaderboard_public'));

        $this->assertSame(3, PolicyAcceptance::where('user_id', $child->id)->where('context', 'parental')->count());
        $this->assertSame('granted', ParentalConsentRequest::where('child_user_id', $child->id)->value('status'));

        Mail::assertSent(ParentalConsentMail::class, fn ($m) => $m->kind === 'granted_guardian' && $m->hasTo('sari@example.test'));
        Mail::assertSent(ParentalConsentMail::class, fn ($m) => $m->kind === 'granted_child' && $m->hasTo('nadia@example.test'));

        // The link now shows the result instead of the form.
        $this->get($url)->assertOk()->assertSee('persetujuan tercatat')->assertDontSee('Setujui &amp; aktifkan akun', false);
    }

    public function test_existing_parent_account_is_linked_on_approval(): void
    {
        $parent = $this->makeUser('parent', 40, ['email' => 'sari@example.test']);
        [$child, $url] = $this->registerChild();

        $this->post($url, $this->grantPayload())->assertRedirect();

        $this->assertTrue($parent->fresh()->isParentOf($child));
        $this->assertSame($parent->id, ParentalConsentRequest::where('child_user_id', $child->id)->value('guardian_user_id'));
        $this->assertSame($parent->id, ConsentRecord::where('subject_user_id', $child->id)->value('actor_user_id'));
    }

    public function test_parent_registering_through_the_invitation_is_linked(): void
    {
        [$child, $url] = $this->registerChild();
        $this->post($url, $this->grantPayload())->assertRedirect();
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->get($url)->assertSee('Buat akun orang tua');

        $this->post('/register', $this->registrationPayload([
            'role' => 'parent', 'name' => 'Sari Putri', 'email' => 'sari@example.test', 'birth_date' => null, 'undangan' => $token,
        ]))->assertRedirect(route('dashboard', absolute: false));

        $this->assertTrue(User::where('email', 'sari@example.test')->firstOrFail()->isParentOf($child));
    }

    public function test_invitation_token_does_not_link_a_different_email(): void
    {
        [$child, $url] = $this->registerChild();
        $this->post($url, $this->grantPayload())->assertRedirect();
        $token = basename(parse_url($url, PHP_URL_PATH));

        $this->post('/register', $this->registrationPayload([
            'role' => 'parent', 'email' => 'someone-else@example.test', 'birth_date' => null, 'undangan' => $token,
        ]))->assertRedirect();

        $this->assertFalse(User::where('email', 'someone-else@example.test')->firstOrFail()->isParentOf($child));
    }

    public function test_guardian_must_declare_guardianship_and_required_consent(): void
    {
        [$child, $url] = $this->registerChild();

        $this->post($url, $this->grantPayload(['guardian_declaration' => null, 'required_consent' => null]))
            ->assertSessionHasErrors(['guardian_declaration', 'required_consent']);

        $this->assertFalse($child->fresh()->is_active);
    }

    public function test_guardian_declines_and_registration_is_erased(): void
    {
        $class = ClassName::factory()->create();
        [$child, $url] = $this->registerChild(['class_id' => $class->id]);

        $this->post($url, ['decision' => 'decline', 'reason' => 'Belum waktunya'])->assertRedirect($url);

        $request = ParentalConsentRequest::where('child_user_id', $child->id)->firstOrFail();
        $this->assertSame('declined', $request->status);
        $this->assertNull($request->guardian_name);
        $this->assertStringNotContainsString('sari@', $request->guardian_email);

        $trashed = User::withTrashed()->findOrFail($child->id);
        $this->assertTrue($trashed->trashed());
        $this->assertStringEndsWith('@users.invalid', $trashed->email);
        $this->assertSame(Enrollment::STATUS_CANCELLED, Enrollment::where('student_id', $child->id)->value('status'));
        $this->assertSame(0, ConsentRecord::where('subject_user_id', $child->id)->count());

        Mail::assertSent(ParentalConsentMail::class, fn ($m) => $m->kind === 'declined' && $m->hasTo('nadia@example.test'));
        $this->get($url)->assertOk()->assertSee('Pendaftaran ditolak');
    }

    public function test_unsigned_or_unknown_links_are_rejected(): void
    {
        [$child, $url] = $this->registerChild();
        $unsigned = strtok($url, '?');

        $this->get($unsigned)->assertForbidden()->assertSee('Tautan tidak valid');
        $this->post($unsigned, $this->grantPayload())->assertForbidden();
        $this->get('/persetujuan-orang-tua/'.str_repeat('a', 64))->assertForbidden();

        $this->assertFalse($child->fresh()->is_active);
    }

    public function test_expired_link_cannot_be_used_and_expiry_command_erases_the_registration(): void
    {
        [$child, $url] = $this->registerChild();

        $this->travel(8)->days();

        $this->get($url)->assertOk()->assertSee('kedaluwarsa');
        $this->post($url, $this->grantPayload())->assertOk();
        $this->assertFalse(User::withTrashed()->find($child->id)->is_active);

        $this->artisan('privacy:expire-parental-consents')->assertSuccessful();

        $this->assertSame('expired', ParentalConsentRequest::where('child_user_id', $child->id)->value('status'));
        $this->assertTrue(User::withTrashed()->findOrFail($child->id)->trashed());
    }

    public function test_resend_issues_a_new_link_and_invalidates_the_old_one(): void
    {
        [$child, $oldUrl] = $this->registerChild();

        $this->post(route('privacy.parental-consent.resend'), ['email' => 'nadia@example.test'])
            ->assertSessionHas('status');

        $newUrl = null;
        Mail::assertSent(ParentalConsentMail::class, function ($m) use (&$newUrl) {
            if ($m->kind === 'request') {
                $newUrl = $m->url;
            }

            return true;
        });

        $this->assertNotSame($oldUrl, $newUrl);
        $this->get($oldUrl)->assertForbidden();
        $this->get($newUrl)->assertOk();
        $this->assertSame(2, ParentalConsentRequest::where('child_user_id', $child->id)->value('send_count'));
    }

    public function test_resend_gives_the_same_answer_for_unknown_emails(): void
    {
        Mail::fake();

        $this->post(route('privacy.parental-consent.resend'), ['email' => 'unknown@example.test'])
            ->assertSessionHas('status');

        Mail::assertNothingSent();
    }
}
