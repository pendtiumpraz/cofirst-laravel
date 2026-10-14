<?php

namespace App\Services\Academic;

use App\Models\Material;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Storage of material files on the PRIVATE disk.
 *
 * Paid course material used to live on the public disk (reachable without login at
 * /storage/materials/<time>_<name>). Files are now stored under storage/app/private
 * with a random name and served only through authorised routes. Legacy files still
 * on the public disk are moved the first time they are requested (or with
 * `php artisan academic:secure-material-files`).
 */
class MaterialFileService
{
    public const DISK = 'local';

    public const DIRECTORY = 'materials';

    /** Allowed extensions (one list for the file picker and the validation). */
    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'mp4'];

    /** Extensions a browser may display inline (never HTML/SVG). */
    public const INLINE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'mp4'];

    /** The accept attribute for the file input. */
    public static function acceptAttribute(): string
    {
        return implode(',', array_map(fn ($ext) => '.'.$ext, self::EXTENSIONS));
    }

    /**
     * Store an uploaded file with a random name on the private disk.
     *
     * Returns:
     *     string: The stored path (materials/<hash>.<ext>).
     */
    public function store(UploadedFile $file): string
    {
        return $file->store(self::DIRECTORY, self::DISK);
    }

    /** Delete a stored file (private disk, and the legacy public copy). */
    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        Storage::disk(self::DISK)->delete($path);
        Storage::disk('public')->delete($path);
    }

    /** Whether the material has a readable file (moves legacy public files first). */
    public function exists(Material $material): bool
    {
        if (blank($material->file_path)) {
            return false;
        }

        $this->secureLegacyFile($material);

        return Storage::disk(self::DISK)->exists($material->file_path);
    }

    /** Lower-case extension of the stored file. */
    public function extension(Material $material): string
    {
        return strtolower(pathinfo((string) $material->file_path, PATHINFO_EXTENSION));
    }

    /** Whether the file can be shown inline (PDF, image, audio, video). */
    public function canInline(Material $material): bool
    {
        return in_array($this->extension($material), self::INLINE_EXTENSIONS, true);
    }

    /** Human-readable size, e.g. "2,4 MB". */
    public function size(Material $material): ?string
    {
        if (! $this->exists($material)) {
            return null;
        }

        $bytes = (int) Storage::disk(self::DISK)->size($material->file_path);

        return match (true) {
            $bytes >= 1048576 => number_format($bytes / 1048576, 1, ',', '.').' MB',
            $bytes >= 1024 => number_format($bytes / 1024, 0, ',', '.').' KB',
            default => $bytes.' B',
        };
    }

    /** Download name: slug of the title plus the real extension. */
    public function downloadName(Material $material): string
    {
        $base = Str::slug($material->title) ?: 'materi-'.$material->id;
        $ext = $this->extension($material);

        return $ext !== '' ? $base.'.'.$ext : $base;
    }

    /**
     * Stream the file as a download, or inline when requested and safe.
     *
     * Args:
     *     material (Material): The material (must have a file).
     *     inline (bool): Display in the browser instead of downloading.
     */
    public function response(Material $material, bool $inline = false): StreamedResponse
    {
        abort_unless($this->exists($material), 404, 'Berkas materi tidak ditemukan.');

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ];

        if ($inline && $this->canInline($material)) {
            return Storage::disk(self::DISK)->response($material->file_path, $this->downloadName($material), $headers);
        }

        return Storage::disk(self::DISK)->download($material->file_path, $this->downloadName($material), $headers);
    }

    /**
     * Move a legacy file from the public disk to the private disk.
     *
     * Returns:
     *     bool: True when a file was moved.
     */
    public function secureLegacyFile(Material $material): bool
    {
        $path = $material->file_path;

        if (blank($path) || Storage::disk(self::DISK)->exists($path) || ! Storage::disk('public')->exists($path)) {
            return false;
        }

        $stream = Storage::disk('public')->readStream($path);
        Storage::disk(self::DISK)->writeStream($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        Storage::disk('public')->delete($path);

        return true;
    }

    /**
     * Move every legacy material file off the public disk.
     *
     * Returns:
     *     int: Number of files moved.
     */
    public function secureAllLegacyFiles(): int
    {
        $moved = 0;

        Material::withTrashed()->whereNotNull('file_path')->orderBy('id')->each(function (Material $material) use (&$moved) {
            $moved += $this->secureLegacyFile($material) ? 1 : 0;
        });

        return $moved;
    }
}
