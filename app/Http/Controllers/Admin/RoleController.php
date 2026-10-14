<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\User;
use App\Services\Admin\AdminAudit;
use App\Support\Admin\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Peran (superadmin only). System roles cannot be renamed or deleted because
 * routes, dashboards and navigation depend on their names; the superadmin role
 * always holds every permission.
 */
class RoleController extends Controller
{
    /**
     * Roles with user and permission counts.
     */
    public function index(): View
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->orderByRaw('case when name in (\''.implode("','", AccessControl::SYSTEM_ROLES).'\') then 0 else 1 end')
            ->orderBy('id')
            ->get();

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissionTotal' => Permission::count(),
        ]);
    }

    /**
     * Create form with the grouped permission matrix.
     */
    public function create(): View
    {
        return view('admin.roles.create', [
            'role' => new Role(),
            'groups' => AccessControl::group(Permission::orderBy('name')->get()),
            'granted' => [],
        ]);
    }

    /**
     * Store a custom role.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $role = DB::transaction(function () use ($request) {
            $role = Role::create(['name' => $request->input('name'), 'guard_name' => 'web']);
            $role->syncPermissions($request->input('permissions', []));

            return $role;
        });

        $this->flushCache();
        AdminAudit::log('role.created', $role, ['permissions' => count($request->input('permissions', []))]);

        return redirect()->route('admin.roles.show', $role)->with('success', 'Peran '.AccessControl::roleLabel($role->name).' dibuat.');
    }

    /**
     * Role detail: permissions per module and a sample of its users.
     */
    public function show(Role $role): View
    {
        $role->load('permissions:id,name');
        $users = User::role($role->name)->orderBy('name')->limit(12)->get(['id', 'name', 'profile_photo_path', 'is_active']);

        return view('admin.roles.show', [
            'role' => $role,
            'groups' => AccessControl::group($role->permissions),
            'users' => $users,
            'userCount' => User::role($role->name)->count(),
        ]);
    }

    /**
     * Edit form.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions:id,name');

        return view('admin.roles.edit', [
            'role' => $role,
            'groups' => AccessControl::group(Permission::orderBy('name')->get()),
            'granted' => $role->permissions->pluck('name')->all(),
        ]);
    }

    /**
     * Update a role (system roles keep their name; superadmin keeps every permission).
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $before = $role->permissions()->pluck('name')->sort()->values()->all();

        DB::transaction(function () use ($request, $role) {
            if (! AccessControl::isSystemRole($role->name)) {
                $role->update(['name' => $request->input('name')]);
            }

            // Reason: the superadmin role must never lose a permission (lockout).
            $role->syncPermissions($role->name === 'superadmin'
                ? Permission::pluck('name')->all()
                : $request->input('permissions', []));
        });

        $this->flushCache();
        $after = $role->permissions()->pluck('name')->sort()->values()->all();
        AdminAudit::log('role.updated', $role, [
            'granted' => array_values(array_diff($after, $before)),
            'revoked' => array_values(array_diff($before, $after)),
        ]);

        return redirect()->route('admin.roles.show', $role)->with('success', 'Peran '.AccessControl::roleLabel($role->name).' diperbarui.');
    }

    /**
     * Delete a custom role that no user holds.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (AccessControl::isSystemRole($role->name)) {
            return back()->with('error', 'Peran bawaan sistem tidak dapat dihapus.');
        }

        $holders = User::role($role->name)->count();
        if ($holders > 0) {
            return back()->with('error', "Peran ini masih dipakai {$holders} pengguna. Pindahkan pengguna ke peran lain terlebih dahulu.");
        }

        $role->delete();
        $this->flushCache();
        AdminAudit::log('role.deleted', null, ['role' => $role->name]);

        return redirect()->route('admin.roles.index')->with('success', 'Peran dihapus.');
    }

    /**
     * Clear spatie's permission cache after changes.
     */
    private function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
