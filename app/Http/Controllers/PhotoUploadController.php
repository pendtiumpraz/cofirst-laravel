<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoUploadController extends Controller
{
    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'crop_data' => 'nullable|json'
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store new photo
        $photo = $request->file('photo');
        $filename = 'profile-photos/' . $user->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
        
        // Store the file directly
        // Note: Cropping functionality requires image manipulation library
        // For now, we'll store the original image
        $path = $photo->storeAs('profile-photos', basename($filename), 'public');
        
        // Update user record
        $user->update([
            'profile_photo_path' => $path,
            'photo_crop_data' => $request->crop_data ? json_decode($request->crop_data, true) : null
        ]);

        return response()->json([
            'success' => true,
            'photo_url' => $user->profile_photo_url,
            'message' => 'Profile photo uploaded successfully'
        ]);
    }

    /**
     * Delete profile photo
     */
    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update([
                'profile_photo_path' => null,
                'photo_crop_data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile photo deleted successfully'
        ]);
    }
}