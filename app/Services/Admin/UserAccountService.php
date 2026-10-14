<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Account lifecycle for the admin user screens: create/update with roles,
 * profile, photo and parent–child links; activate/deactivate (ending sessions);
 * archive (soft delete) and restore. Every change is audited without personal
 * values (UU PDP: accountability, data minimisation in logs).
 */
class UserAccountService
{
    /** Profile columns admins may fill (data minimisation: nothing else). */
    public const PROFILE_FIELDS = ['full_name', 'phone_number', 'birth_date', 'gender', 'address', 'bio'];

    private const PHOTO_DIR = 'profile-photos';

    /**
     * Create an account.
     *
     * Args:
     *     data (array): Validated input (name, email, password?, roles, is_active,
     *         send_invite, profile fields, children[], parents[]).
     *     photo (UploadedFile|null): Optional profile photo.
     *
     * Returns:
     *     User: The new user (roles and profile loaded).
     */
    public function create(array $data, ?UploadedFile $photo = null): User
    {
        $storedPhoto = null;

        try {
            $user = DB::transaction(function () use ($data, $photo, &$storedPhoto) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    // Reason: with an invite the person sets their own password via the
                    // reset link; the random one is never shown to anybody.
                    'password' => ! empty($data['send_invite']) || empty($data['password'])
                        ? Str::password(32)
                        : $data['password'],
                    'is_active' => (bool) ($data['is_active'] ?? true),
                ]);

                $user->syncRoles($data['roles']);
                $this->saveProfile($user, $data);

                if ($photo) {
                    $storedPhoto = $photo->store(self::PHOTO_DIR, 'public');
                    $user->forceFill(['profile_photo_path' => $storedPhoto])->save();
                }

                $this->syncFamily($user, $data);

                return $user;
            });
        } catch (Throwable $e) {
            if ($storedPhoto) {
                Storage::disk('public')->delete($storedPhoto);
            }

            throw $e;
        }

        AdminAudit::log('user.created', $user, ['roles' => $data['roles'], 'invite' => ! empty($data['send_invite'])]);

        if (! empty($data['send_invite'])) {
            $this->sendPasswordLink($user);
        }

        return $user->load('roles', 'profile');
    }

    /**
     * Update an account.
     *
     * Args:
     *     user (User): The account.
     *     data (array): Validated input; `roles` is ignored when null (self-edit).
     *     photo (UploadedFile|null): Replacement photo.
     *     removePhoto (bool): Delete the current photo.
     *
     * Returns:
     *     array{user: User, unlinked: int}: The user and how many family links were
     *     removed because the parent/student role was taken away.
     */
    public function update(User $user, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): array
    {
        $oldPhoto = $user->profile_photo_path;
        $storedPhoto = null;
        $unlinked = 0;
        $oldRoles = $user->getRoleNames()->sort()->values()->all();

        try {
            DB::transaction(function () use ($user, $data, $photo, $removePhoto, &$storedPhoto, &$unlinked) {
                $attributes = Arr::only($data, ['name', 'email']);
                if (array_key_exists('is_active', $data)) {
                    $attributes['is_active'] = (bool) $data['is_active'];
                }
                if (! empty($data['password'])) {
                    $attributes['password'] = $data['password'];
                }

                if ($photo) {
                    $storedPhoto = $photo->store(self::PHOTO_DIR, 'public');
                    $attributes['profile_photo_path'] = $storedPhoto;
                } elseif ($removePhoto) {
                    $attributes['profile_photo_path'] = null;
                }

                $user->fill($attributes)->save();

                if (isset($data['roles']) && is_array($data['roles'])) {
                    $user->syncRoles($data['roles']);
                    $user->unsetRelation('roles');

                    // Reason: a parent link only makes sense while both roles exist.
                    if (! $user->hasRole('parent')) {
                        $unlinked += $user->children()->detach();
                    }
                    if (! $user->hasRole('student')) {
                        $unlinked += $user->parents()->detach();
                    }
                }

                $this->saveProfile($user, $data);
                $this->syncFamily($user, $data);
            });
        } catch (Throwable $e) {
            if ($storedPhoto) {
                Storage::disk('public')->delete($storedPhoto);
            }

            throw $e;
        }

        if (($storedPhoto || $removePhoto) && $oldPhoto && $oldPhoto !== $storedPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        if (array_key_exists('is_active', $data) && ! $data['is_active']) {
            $this->endSessions($user);
        }

        $newRoles = $user->getRoleNames()->sort()->values()->all();
        AdminAudit::log('user.updated', $user, [
            'fields' => array_values(array_intersect(array_keys($data), array_merge(['name', 'email', 'password', 'is_active'], self::PROFILE_FIELDS))),
            'roles_before' => $oldRoles,
            'roles_after' => $newRoles,
            'photo' => $storedPhoto ? 'replaced' : ($removePhoto ? 'removed' : null),
            'family_links_removed' => $unlinked,
        ]);

        return ['user' => $user->load('roles', 'profile'), 'unlinked' => $unlinked];
    }

    /**
     * Activate or deactivate an account; deactivation ends its sessions.
     */
    public function setActive(User $user, bool $active): void
    {
        $user->forceFill(['is_active' => $active])->save();

        if (! $active) {
            $this->endSessions($user);
        }

        AdminAudit::log($active ? 'user.activated' : 'user.deactivated', $user);
    }

    /**
     * Archive (soft delete) an account: deactivate, end sessions, then delete().
     *
     * Payments, reports and certificates stay linked (financial retention duty);
     * permanent erasure is handled by the privacy module (DSR / retention).
     */
    public function archive(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill(['is_active' => false])->save();
            $user->delete();
        });

        $this->endSessions($user);
        AdminAudit::log('user.archived', $user);
    }

    /**
     * Restore an archived account (stays inactive until an admin activates it).
     */
    public function restore(User $user): void
    {
        $user->restore();
        AdminAudit::log('user.restored', $user);
    }

    /**
     * Link a parent and a student.
     *
     * Args:
     *     parent (User): Holds the parent role.
     *     student (User): Holds the student role.
     *     context (array): Non-personal evidence details (relationship, method).
     */
    public function linkFamily(User $parent, User $student, array $context = []): bool
    {
        $changes = $parent->children()->syncWithoutDetaching([$student->id]);
        $linked = $changes['attached'] !== [];

        if ($linked) {
            AdminAudit::log('family.linked', $student, ['parent_id' => $parent->id] + Arr::only($context, ['relationship', 'verification']));
        }

        return $linked;
    }

    /**
     * Remove a parent–student link.
     */
    public function unlinkFamily(User $parent, User $student): bool
    {
        $removed = $parent->children()->detach($student->id) > 0;

        if ($removed) {
            AdminAudit::log('family.unlinked', $student, ['parent_id' => $parent->id]);
        }

        return $removed;
    }

    /**
     * Send the password set/reset link (used as an invitation).
     *
     * Returns:
     *     bool: Whether the broker accepted the request.
     */
    public function sendPasswordLink(User $user): bool
    {
        try {
            $status = Password::broker()->sendResetLink(['email' => $user->email]);
        } catch (Throwable $e) {
            report($e);

            return false;
        }

        AdminAudit::log('user.password_link_sent', $user);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * End every session of the user and rotate the remember-me token so a
     * deactivated or archived account loses access immediately.
     */
    public function endSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
    }

    /**
     * Create or update the profile when any profile field was submitted.
     */
    private function saveProfile(User $user, array $data): void
    {
        $fields = Arr::only($data, self::PROFILE_FIELDS);
        if ($fields === []) {
            return;
        }

        $hasValue = collect($fields)->filter(fn ($v) => filled($v))->isNotEmpty();
        if (! $hasValue && ! $user->profile()->exists()) {
            return;
        }

        $fields['full_name'] = filled($fields['full_name'] ?? null) ? $fields['full_name'] : $user->name;
        $user->profile()->updateOrCreate(['user_id' => $user->id], $fields);
        $user->unsetRelation('profile');
    }

    /**
     * Sync parent–child links from the form (children for parents, parents for students).
     */
    private function syncFamily(User $user, array $data): void
    {
        if (array_key_exists('children', $data) && $user->hasRole('parent')) {
            $changes = $user->children()->sync(array_map('intval', (array) $data['children']));
            foreach ($changes['attached'] as $id) {
                AdminAudit::log('family.linked', $user, ['student_id' => (int) $id]);
            }
            foreach ($changes['detached'] as $id) {
                AdminAudit::log('family.unlinked', $user, ['student_id' => (int) $id]);
            }
        }

        if (array_key_exists('parents', $data) && $user->hasRole('student')) {
            $changes = $user->parents()->sync(array_map('intval', (array) $data['parents']));
            foreach ($changes['attached'] as $id) {
                AdminAudit::log('family.linked', $user, ['parent_id' => (int) $id]);
            }
            foreach ($changes['detached'] as $id) {
                AdminAudit::log('family.unlinked', $user, ['parent_id' => (int) $id]);
            }
        }
    }
}
