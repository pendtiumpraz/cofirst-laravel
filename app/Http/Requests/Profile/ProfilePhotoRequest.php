<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Profil › Foto profil upload. Type checks use the file content (mimetypes/image),
 * never the client file name; ProfilePhotoService double-checks with getimagesize().
 */
class ProfilePhotoRequest extends FormRequest
{
    /** Maximum upload size in kilobytes (5 MB: a typical phone photo). */
    public const MAX_KB = 5120;

    /**
     * Signed-in users change their own photo only.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'file',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:'.self::MAX_KB,
                'dimensions:min_width=64,min_height=64,max_width=8000,max_height=8000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['photo' => 'foto profil'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Pilih foto yang ingin diunggah.',
            'photo.image' => 'Berkas harus berupa gambar JPG, PNG, atau WebP.',
            'photo.mimetypes' => 'Berkas harus berupa gambar JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
            'photo.dimensions' => 'Ukuran foto minimal 64×64 piksel dan maksimal 8000×8000 piksel.',
            'photo.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak lebih dari 5 MB.',
        ];
    }
}
