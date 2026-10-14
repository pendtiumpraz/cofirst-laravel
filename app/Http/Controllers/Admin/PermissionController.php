<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use App\Services\Admin\AdminAudit;
use App\Support\Admin\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Izin akses (superadmin only). Built-in permissions are referenced by the
 * admin policies and cannot be renamed or deleted; custom permissions can.
 */
class PermissionController extends Controller
{
    /**
     * Permissions grouped by module, with the roles that hold them.
     */
    public function index(Request $request): View
    {
        $permissions = Permission::query()
            ->with('roles:id,name')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%'))
            ->orderBy('name')
            ->get();

        return view('admin.permissions.index', [
            'groups' => AccessControl::group($permissions),
            'total' => Permission::count(),
        ]);
    }

    /**
     * Create form.
     */
    public function create(): View
    {
        return view('admin.permissions.create', [
            'permission' => new Permission(),
            'roleOptions' => $this->roleOptions(),
            'granted' => [],
        ]);
    }

    /**
     * Store a custom permission and grant it to the chosen roles.
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        $permission = DB::transaction(function () use ($request) {
            $permission = Permission::create(['name' => $request->input('name'), 'guard_name' => 'web']);
            $permission->syncRoles($this->withSuperadmin($request->input('roles', [])));

            return $permission;
        });

        $this->flushCache();
        AdminAudit::log('permission.created', $permission);

        return redirect()->route('admin.permissions.index')->with('success', "Izin \"{$permission->name}\" dibuat.");
    }

    /**
     * Permission detail.
     */
    public function show(Permission $permission): View
    {
        $permission->load('roles:id,name');

        return view('admin.permissions.show', [
            'permission' => $permission,
            'isSystem' => AccessControl::isSystemPermission($permission->name),
        ]);
    }

    /**
     * Edit form (custom permissions only; built-in ones can only change roles).
     */
    public function edit(Permission $permission): View
    {
        $permission->load('roles:id,name');

        return view('admin.permissions.edit', [
            'permission' => $permission,
            'isSystem' => AccessControl::isSystemPermission($permission->name),
            'roleOptions' => $this->roleOptions(),
            'granted' => $permission->roles->pluck('name')->all(),
        ]);
    }

    /**
     * Update a permission's roles (and name, for custom permissions).
     */
    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        DB::transaction(function () use ($request, $permission) {
            if (! AccessControl::isSystemPermission($permission->name)) {
                $permission->update(['name' => $request->input('name')]);
            }
            $permission->syncRoles($this->withSuperadmin($request->input('roles', [])));
        });

        $this->flushCache();
        AdminAudit::log('permission.updated', $permission, ['roles' => $permission->roles()->pluck('name')->all()]);

        return redirect()->route('admin.permissions.show', $permission)->with('success', 'Izin diperbarui.');
    }

    /**
     * Delete a custom permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        if (AccessControl::isSystemPermission($permission->name)) {
            return back()->with('error', 'Izin bawaan sistem tidak dapat dihapus karena dipakai aplikasi.');
        }

        $name = $permission->name;
        $permission->delete();
        $this->flushCache();
        AdminAudit::log('permission.deleted', null, ['permission' => $name]);

        return redirect()->route('admin.permissions.index')->with('success', "Izin \"{$name}\" dihapus.");
    }

    /**
     * Role checkboxes: name => label.
     *
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        return Role::orderBy('id')->pluck('name')
            ->reject(fn ($name) => $name === 'superadmin')
            ->mapWithKeys(fn ($name) => [$name => AccessControl::roleLabel($name)])
            ->all();
    }

    /**
     * Superadmin always holds every permission.
     *
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function withSuperadmin(array $roles): array
    {
        $roles = array_values(array_unique($roles));
        if (Role::where('name', 'superadmin')->exists() && ! in_array('superadmin', $roles, true)) {
            $roles[] = 'superadmin';
        }

        return $roles;
    }

    /**
     * Clear spatie's permission cache after changes.
     */
    private function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
