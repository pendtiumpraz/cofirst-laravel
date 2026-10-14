<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\Concerns\AdminTestHelpers;
use Tests\TestCase;

/**
 * Peran & Izin (superadmin): system roles/permissions are protected and
 * permissions are actually enforced by the admin policies.
 */
class RolePermissionTest extends TestCase
{
    use AdminTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    public function test_superadmin_manages_custom_roles(): void
    {
        $super = $this->superadmin();

        $this->actingAs($super)->get(route('admin.roles.index'))->assertOk()->assertSee('Superadmin');
        $this->actingAs($super)->get(route('admin.roles.create'))->assertOk();

        $this->actingAs($super)->post(route('admin.roles.store'), ['name' => 'Koordinator_Kelas', 'permissions' => ['view classes']])
            ->assertSessionHasNoErrors();
        $role = Role::findByName('koordinator_kelas');
        $this->assertTrue($role->hasPermissionTo('view classes'));

        $this->actingAs($super)->get(route('admin.roles.show', $role))->assertOk();
        $this->actingAs($super)->get(route('admin.roles.edit', $role))->assertOk();
        $this->actingAs($super)->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertNull(Role::where('name', 'koordinator_kelas')->first());
    }

    public function test_role_names_must_be_safe_for_middleware(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.roles.store'), ['name' => 'admin|superadmin'])
            ->assertSessionHasErrors('name');
    }

    public function test_system_roles_cannot_be_renamed_or_deleted_and_superadmin_keeps_all_permissions(): void
    {
        $super = $this->superadmin();
        $admin = Role::findByName('admin');
        $superRole = Role::findByName('superadmin');

        $this->actingAs($super)->put(route('admin.roles.update', $admin), ['name' => 'administrator', 'permissions' => ['view users']])
            ->assertSessionHasNoErrors();
        $this->assertSame('admin', $admin->fresh()->name);
        $this->assertSame(['view users'], $admin->fresh()->permissions->pluck('name')->all());

        $this->actingAs($super)->delete(route('admin.roles.destroy', $admin))->assertSessionHas('error');
        $this->assertNotNull(Role::where('name', 'admin')->first());

        $this->actingAs($super)->put(route('admin.roles.update', $superRole), ['name' => 'superadmin', 'permissions' => []]);
        $this->assertSame(Permission::count(), $superRole->fresh()->permissions()->count());
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $super = $this->superadmin();
        $role = Role::create(['name' => 'relawan', 'guard_name' => 'web']);
        $this->student()->assignRole($role);

        $this->actingAs($super)->delete(route('admin.roles.destroy', $role))->assertSessionHas('error');
        $this->assertNotNull(Role::where('name', 'relawan')->first());
    }

    public function test_permissions_crud_protects_system_permissions(): void
    {
        $super = $this->superadmin();

        $this->actingAs($super)->get(route('admin.permissions.index'))->assertOk()->assertSee('Melihat daftar');
        $this->actingAs($super)->post(route('admin.permissions.store'), ['name' => 'Export Enrollments', 'roles' => ['admin']])
            ->assertSessionHasNoErrors();
        $custom = Permission::findByName('export enrollments');
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo($custom));
        $this->assertTrue(Role::findByName('superadmin')->hasPermissionTo($custom));

        $system = Permission::findByName('view users');
        $this->actingAs($super)->get(route('admin.permissions.show', $system))->assertOk();
        $this->actingAs($super)->get(route('admin.permissions.edit', $system))->assertOk();
        $this->actingAs($super)->delete(route('admin.permissions.destroy', $system))->assertSessionHas('error');
        $this->assertNotNull(Permission::where('name', 'view users')->first());

        $this->actingAs($super)->delete(route('admin.permissions.destroy', $custom))->assertRedirect(route('admin.permissions.index'));
        $this->assertNull(Permission::where('name', 'export enrollments')->first());
    }

    public function test_admin_cannot_open_roles_and_permissions(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.permissions.store'), ['name' => 'x y'])->assertForbidden();
    }

    public function test_granting_a_permission_takes_effect(): void
    {
        $super = $this->superadmin();
        $admin = $this->admin();
        $student = $this->student();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $student))->assertForbidden();

        $granted = Role::findByName('admin')->permissions->pluck('name')->push('delete users')->all();
        $this->actingAs($super)->put(route('admin.roles.update', Role::findByName('admin')), ['name' => 'admin', 'permissions' => $granted])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin->fresh())->delete(route('admin.users.destroy', $student))->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $student->id]);
    }
}
