<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

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
        
        // Process image with Intervention
        $image = Image::make($photo);
        
        // Apply crop if crop data provided
        if ($request->has('crop_data')) {
            $cropData = json_decode($request->crop_data, true);
            $image->crop(
                (int)$cropData['width'],
                (int)$cropData['height'],
                (int)$cropData['x'],
                (int)$cropData['y']
            );
        }
        
        // Resize to standard size
        $image->fit(300, 300);
        
        // Save to storage
        Storage::disk('public')->put($filename, $image->encode());
        
        // Update user record
        $user->update([
            'profile_photo_path' => $filename,
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