<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Support\Images\ImageMetadataStripper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Stores and removes users' profile photos safely.
 *
 * - The file type is detected from the content (finfo + getimagesize), never from the
 *   client file name; the stored extension is derived from the detected type.
 * - Files get a random 40-character name, so URLs cannot be guessed from a user id.
 * - EXIF/XMP/text metadata (GPS location, device, owner) is stripped before storing.
 * - The new file is written before the old one is deleted, so a failed upload never
 *   leaves the user without a photo.
 */
class ProfilePhotoService
{
    public const DIRECTORY = 'profile-photos';

    public const DISK = 'public';

    /** Detected mime type => [stored extension, getimagesize() IMAGETYPE_*]. */
    public const TYPES = [
        'image/jpeg' => ['jpg', IMAGETYPE_JPEG],
        'image/png' => ['png', IMAGETYPE_PNG],
        'image/webp' => ['webp', IMAGETYPE_WEBP],
    ];

    /**
     * Replace the user's profile photo with the uploaded image.
     *
     * Args:
     *     user (User): Owner of the photo.
     *     file (UploadedFile): Validated upload.
     *
     * Returns:
     *     string: The stored path relative to the public disk.
     *
     * Raises:
     *     ValidationException: When the content is not a supported image.
     */
    public function replace(User $user, UploadedFile $file): string
    {
        [$extension, $mime] = $this->detect($file);

        $bytes = file_get_contents($file->getRealPath());
        if ($bytes === false) {
            throw ValidationException::withMessages(['photo' => 'Berkas foto tidak dapat dibaca. Coba unggah ulang.']);
        }

        $path = self::DIRECTORY.'/'.Str::random(40).'.'.$extension;
        Storage::disk(self::DISK)->put($path, ImageMetadataStripper::strip($bytes, $mime));

        $previous = $user->profile_photo_path;

        try {
            $user->forceFill(['profile_photo_path' => $path, 'photo_crop_data' => null])->save();
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($path);

            throw $e;
        }

        $this->deleteFile($previous);

        return $path;
    }

    /**
     * Remove the user's profile photo (file and reference).
     *
     * Returns:
     *     bool: True when there was a photo to remove.
     */
    public function remove(User $user): bool
    {
        $previous = $user->profile_photo_path;

        if (! $previous) {
            return false;
        }

        $user->forceFill(['profile_photo_path' => null, 'photo_crop_data' => null])->save();
        $this->deleteFile($previous);

        return true;
    }

    /**
     * Detect the real image type from the file content.
     *
     * @return array{0:string, 1:string} [extension, mime]
     */
    private function detect(UploadedFile $file): array
    {
        $mime = (string) $file->getMimeType();
        $info = @getimagesize($file->getRealPath());

        // Reason: both detectors must agree, which rejects polyglots whose magic bytes
        // and image header disagree (e.g. a GIF header followed by HTML).
        if (! isset(self::TYPES[$mime]) || $info === false || $info[2] !== self::TYPES[$mime][1]) {
            throw ValidationException::withMessages([
                'photo' => 'Berkas harus berupa gambar JPG, PNG, atau WebP yang valid.',
            ]);
        }

        return [self::TYPES[$mime][0], $mime];
    }

    /**
     * Delete a previously stored photo, only inside the profile-photos directory.
     */
    private function deleteFile(?string $path): void
    {
        if (! $path || str_contains($path, '..') || ! str_starts_with($path, self::DIRECTORY.'/')) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
    }
}
