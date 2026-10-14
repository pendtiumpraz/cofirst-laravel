<?php

namespace Tests\Feature\Admin;

use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Admin › Pengguna: CRUD, role escalation guards, family links and lifecycle.
 */
class UserManagementTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    /** A valid 1×1 transparent PNG. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Andi Pratama',
            'email' => 'andi@example.test',
            'roles' => ['student'],
            'is_active' => '1',
            'send_invite' => '0',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ], $overrides);
    }

    public function test_admin_can_list_and_filter_users(): void
    {
        $admin = $this->admin();
        $student = $this->student(['name' => 'Siti Murid']);
        $this->teacher(['name' => 'Budi Guru']);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()->assertSee('Siti Murid')->assertSee('Budi Guru');

        $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'student']))
            ->assertOk()->assertSee('Siti Murid')->assertDontSee('Budi Guru');

        $this->actingAs($admin)->get(route('admin.users.show', $student))->assertOk()->assertSee('Siti Murid');
        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.edit', $student))->assertOk();
    }

    public function test_admin_creates_student_with_profile_and_photo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), $this->validPayload([
            'phone_number' => '0812 3456 7890',
            'birth_date' => now()->subYears(10)->toDateString(),
            // Reason: the local PHP has no GD, so fake()->image() is unavailable; a real 1×1 PNG works.
            'photo' => UploadedFile::fake()->createWithContent('foto.png', base64_decode(self::PNG)),
        ]));

        $user = User::where('email', 'andi@example.test')->firstOrFail();
        $response->assertRedirect(route('admin.users.show', $user));
        $this->assertTrue($user->hasRole('student'));
        $this->assertSame('0812 3456 7890', $user->profile->phone_number);
        $this->assertTrue($user->isChild());
        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringNotContainsString('foto.png', $user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_invite_sends_password_link_instead_of_requiring_a_password(): void
    {
        Notification::fake();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->validPayload([
            'send_invite' => '1', 'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasNoErrors();

        Notification::assertSentTo(User::where('email', 'andi@example.test')->first(), ResetPassword::class);
    }

    public function test_password_is_required_without_invite(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), $this->validPayload(['password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'andi@example.test']);
    }

    public function test_admin_cannot_grant_superadmin_or_admin(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->validPayload(['roles' => ['superadmin']]))
            ->assertSessionHasErrors('roles.0');
        $this->actingAs($admin)->post(route('admin.users.store'), $this->validPayload(['roles' => ['admin']]))
            ->assertSessionHasErrors('roles.0');

        $this->assertDatabaseMissing('users', ['email' => 'andi@example.test']);
    }

    public function test_superadmin_can_grant_admin(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.users.store'), $this->validPayload(['roles' => ['admin']]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(User::where('email', 'andi@example.test')->first()->hasRole('admin'));
    }

    public function test_admin_cannot_touch_superadmin_accounts(): void
    {
        $admin = $this->admin();
        $super = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.users.edit', $super))->assertForbidden();
        $this->actingAs($admin)->put(route('admin.users.update', $super), $this->validPayload([
            'email' => 'hijack@example.test', 'roles' => ['student'],
        ]))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.users.toggle-status', $super))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $super))->assertForbidden();

        $this->assertSame($super->email, $super->fresh()->email);
        $this->assertTrue($super->fresh()->is_active);
    }

    public function test_admin_cannot_change_own_roles_or_deactivate_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'roles' => ['superadmin'],
        ])->assertSessionHasErrors('roles');
        $this->assertFalse($admin->fresh()->hasRole('superadmin'));

        // Editing yourself without roles works and never deactivates you.
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => 'Admin Baru', 'email' => $admin->email, 'is_active' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Admin Baru', $admin->fresh()->name);
        $this->assertTrue($admin->fresh()->is_active);

        $this->actingAs($admin)->post(route('admin.users.toggle-status', $admin))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
    }

    public function test_update_keeps_multiple_roles_and_unlinks_children_when_parent_role_removed(): void
    {
        $super = $this->superadmin();
        $parent = $this->parentUser();
        $child = $this->student();
        $parent->children()->attach($child->id);

        $this->actingAs($super)->put(route('admin.users.update', $parent), [
            'name' => $parent->name, 'email' => $parent->email, 'roles' => ['parent', 'student'], 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($parent->fresh()->hasAllRoles(['parent', 'student']));
        $this->assertTrue($parent->fresh()->isParentOf($child));

        $this->actingAs($super)->put(route('admin.users.update', $parent), [
            'name' => $parent->name, 'email' => $parent->email, 'roles' => ['student'], 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($parent->fresh()->isParentOf($child));
    }

    public function test_family_link_requires_verification_and_correct_roles(): void
    {
        $admin = $this->admin();
        $parent = $this->parentUser();
        $child = $this->student();
        $teacher = $this->teacher();

        $this->actingAs($admin)->post(route('admin.users.family.attach', $child), ['related_id' => $parent->id])
            ->assertSessionHasErrors('guardian_verified');
        $this->assertFalse($parent->isParentOf($child));

        $this->actingAs($admin)->post(route('admin.users.family.attach', $child), ['related_id' => $teacher->id, 'guardian_verified' => '1'])
            ->assertSessionHasErrors('related_id');

        $this->actingAs($admin)->post(route('admin.users.family.attach', $child), ['related_id' => $parent->id, 'guardian_verified' => '1', 'relationship' => 'ibu'])
            ->assertSessionHasNoErrors();
        $this->assertTrue($parent->fresh()->isParentOf($child));

        $this->actingAs($admin)->delete(route('admin.users.family.detach', [$child, $parent]))->assertRedirect();
        $this->assertFalse($parent->fresh()->isParentOf($child));
    }

    public function test_form_links_children_only_with_student_accounts(): void
    {
        $admin = $this->admin();
        $other = $this->teacher();

        $this->actingAs($admin)->post(route('admin.users.store'), $this->validPayload([
            'roles' => ['parent'], 'family_form' => '1', 'children' => [$other->id], 'guardian_verified' => '1',
        ]))->assertSessionHasErrors('children.0');
    }

    public function test_archive_is_soft_delete_and_keeps_payments_then_restore(): void
    {
        $super = $this->superadmin();
        $student = $this->student();
        $payment = FinancialTransaction::factory()->paid()->create(['student_id' => $student->id]);

        $this->actingAs($super)->delete(route('admin.users.destroy', $student))->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $student->id]);
        $this->assertDatabaseHas('financial_transactions', ['id' => $payment->id, 'student_id' => $student->id]);
        $this->actingAs($super)->get(route('admin.users.index', ['status' => 'archived']))->assertOk()->assertSee($student->name);
        $this->actingAs($super)->get(route('admin.users.show', $student->id))->assertOk()->assertSee('Akun ini diarsipkan');

        $this->actingAs($super)->post(route('admin.users.restore', $student->id))->assertRedirect();
        $this->assertNotSoftDeleted('users', ['id' => $student->id]);
        $this->assertFalse($student->fresh()->is_active);
    }

    public function test_admin_without_delete_permission_cannot_archive(): void
    {
        // RoleSeeder gives admins no "delete users" permission (least privilege).
        $this->actingAs($this->admin())->delete(route('admin.users.destroy', $this->student()))->assertForbidden();
    }

    public function test_deactivation_rotates_remember_token(): void
    {
        $admin = $this->admin();
        $student = $this->student();
        $token = $student->remember_token;

        $this->actingAs($admin)->post(route('admin.users.toggle-status', $student))->assertRedirect();

        $this->assertFalse($student->fresh()->is_active);
        $this->assertNotSame($token, $student->fresh()->remember_token);
    }

    public function test_search_returns_names_without_emails(): void
    {
        $admin = $this->admin();
        $this->student(['name' => 'Rina Kartika', 'email' => 'rina.kartika@example.test']);

        $response = $this->actingAs($admin)->getJson(route('admin.users.search', ['role' => 'student', 'q' => 'Rina']))->assertOk();

        $response->assertJsonPath('data.0.name', 'Rina Kartika');
        $this->assertStringNotContainsString('rina.kartika@example.test', $response->getContent());
    }

    public function test_non_staff_are_forbidden(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($student)->getJson(route('admin.users.search', ['q' => 'an']))->assertForbidden();
        $this->actingAs($this->teacher())->post(route('admin.users.store'), $this->validPayload())->assertForbidden();
    }
}
