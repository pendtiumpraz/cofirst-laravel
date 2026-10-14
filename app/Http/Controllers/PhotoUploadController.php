<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\ProfilePhotoRequest;
use App\Services\Auth\PrivacyBridge;
use App\Services\Auth\ProfilePhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Profil › Foto profil: upload and removal of the signed-in user's own photo.
 *
 * Answers JSON for script callers and redirects back to the profile otherwise.
 */
class PhotoUploadController extends Controller
{
    /**
     * Upload (or replace) the profile photo.
     */
    public function uploadProfilePhoto(ProfilePhotoRequest $request, ProfilePhotoService $photos): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $photos->replace($user, $request->file('photo'));

        $message = 'Foto profil berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'photo_url' => $user->profile_photo_url,
                'message' => $message,
            ]);
        }

        return redirect()->to(route('profile.edit').'#foto')->with('success', $message);
    }

    /**
     * Delete the profile photo (file removed from storage).
     */
    public function deleteProfilePhoto(Request $request, ProfilePhotoService $photos): JsonResponse|RedirectResponse
    {
        $removed = $photos->remove($request->user());

        if ($removed) {
            PrivacyBridge::audit('account.photo.deleted', $request->user());
        }

        $message = $removed ? 'Foto profil telah dihapus.' : 'Anda belum memiliki foto profil.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->to(route('profile.edit').'#foto')->with($removed ? 'success' : 'info', $message);
    }
}
