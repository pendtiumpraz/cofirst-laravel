<?php

namespace Tests\Feature\Privacy\Consent;

use App\Models\Privacy\ConsentPurpose;
use App\Models\Privacy\ConsentRecord;
use App\Services\Privacy\ConsentService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

/**
 * ConsentService API used by other modules: append-only ledger, withdrawal,
 * re-consent after a version bump, children and bulk checks.
 */
class ConsentServiceTest extends TestCase
{
    use ConsentTestHelpers, RefreshDatabase;

    private ConsentService $consents;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPrivacy();
        $this->consents = app(ConsentService::class);
    }

    public function test_grant_and_withdraw_are_appended_to_the_history(): void
    {
        $user = $this->makeUser('parent', 40);
        $request = Request::create('/', 'POST', server: ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'PHPUnit']);

        $this->assertFalse($this->consents->hasConsent($user, 'marketing_communications'));

        $this->consents->grant($user, 'marketing_communications', 'privacy_centre', $user, [], $request);
        $this->assertTrue(app(ConsentService::class)->hasConsent($user, 'marketing_communications'));

        $this->consents->withdraw($user, 'marketing_communications', 'privacy_centre', $user, [], $request);
        $fresh = app(ConsentService::class);
        $this->assertFalse($fresh->hasConsent($user, 'marketing_communications'));

        $history = $fresh->history($user);
        $this->assertSame(['withdrawn', 'granted'], $history->pluck('decision')->all());
        $this->assertSame('203.0.113.7', $history->first()->ip_address);
        $this->assertSame('self', $history->first()->actor_role);
        $this->assertNotEmpty($history->first()->evidence['text_hash']);
        $this->assertSame('1.0', $history->first()->evidence['notice_version']);
    }

    public function test_ledger_rows_cannot_be_changed_or_deleted(): void
    {
        $user = $this->makeUser('parent', 40);
        $record = $this->consents->grant($user, 'marketing_communications', 'privacy_centre', $user);

        try {
            $record->update(['decision' => 'withdrawn']);
            $this->fail('Ledger row was updated.');
        } catch (LogicException) {
        }

        $this->expectException(LogicException::class);
        $record->delete();
    }

    public function test_version_bump_requires_fresh_consent(): void
    {
        $user = $this->makeUser('parent', 40);
        $this->consents->grant($user, 'marketing_communications', 'registration', $user);

        ConsentPurpose::where('key', 'marketing_communications')->increment('version');

        $this->assertFalse(app(ConsentService::class)->hasConsent($user, 'marketing_communications'));
    }

    public function test_child_cannot_consent_alone_but_a_linked_parent_can(): void
    {
        $child = $this->makeUser('student', 10);
        $parent = $this->makeUser('parent', 38);
        $parent->children()->attach($child->id);

        try {
            $this->consents->grant($child, 'media_publication', 'privacy_centre', $child);
            $this->fail('A child consented without a guardian.');
        } catch (DomainException) {
        }

        $record = $this->consents->grant($child, 'media_publication', 'privacy_centre', $parent);
        $this->assertSame('guardian', $record->actor_role);
        $this->assertTrue($this->consents->hasConsent($child, 'media_publication'));

        // Withdrawal is always possible, also by the child.
        $this->consents->withdraw($child, 'media_publication', 'privacy_centre', $child);
        $this->assertFalse($this->consents->hasConsent($child, 'media_publication'));
    }

    public function test_non_consent_purposes_are_allowed_and_unknown_ones_fail_closed(): void
    {
        $user = $this->makeUser('student', 20);

        $this->assertTrue($this->consents->hasConsent($user, 'essential_service'));
        $this->assertFalse($this->consents->hasConsent($user, 'does_not_exist'));

        $this->expectException(InvalidArgumentException::class);
        $this->consents->grant($user, 'does_not_exist', 'privacy_centre', $user);
    }

    public function test_bulk_lookup_returns_only_current_grants(): void
    {
        $a = $this->makeUser('student', 20);
        $b = $this->makeUser('student', 21);
        $c = $this->makeUser('student', 22);

        $this->consents->grant($a, 'leaderboard_public', 'privacy_centre', $a);
        $this->consents->grant($b, 'leaderboard_public', 'privacy_centre', $b);
        $this->consents->withdraw($b, 'leaderboard_public', 'privacy_centre', $b);

        $this->assertSame([$a->id], $this->consents->subjectIdsWithConsent('leaderboard_public', [$a->id, $b->id, $c->id]));
    }

    public function test_purposes_for_respects_roles_and_age(): void
    {
        $child = $this->makeUser('student', 10);
        $adult = $this->makeUser('student', 30);

        $childKeys = $this->consents->purposesFor($child, true)->pluck('key');
        $adultKeys = $this->consents->purposesFor($adult, true)->pluck('key');

        $this->assertTrue($childKeys->contains('child_data_processing'));
        $this->assertFalse($childKeys->contains('marketing_communications'));
        $this->assertFalse($childKeys->contains('cookies_analytics'));
        $this->assertFalse($adultKeys->contains('child_data_processing'));
        $this->assertTrue($adultKeys->contains('marketing_communications'));
        $this->assertSame(0, ConsentRecord::count());
    }
}
