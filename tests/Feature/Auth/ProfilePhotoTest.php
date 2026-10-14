<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Auth\Concerns\BuildsTestImages;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use BuildsTestImages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_jpeg_is_stored_under_a_random_name_without_metadata(): void
    {
        $user = User::factory()->student()->create();

        $response = $this->actingAs($user)->post(route('profile.photo.upload'), [
            'photo' => $this->uploadedFile($this->jpegBytes(orientation: 6), 'IMG_2026 rumah kami.JPG'),
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit').'#foto');

        $path = $user->refresh()->profile_photo_path;
        $this->assertMatchesRegularExpression('#^profile-photos/[A-Za-z0-9]{40}\.jpg$#', $path);
        Storage::disk('public')->assertExists($path);

        $stored = Storage::disk('public')->get($path);
        $this->assertStringNotContainsString(self::SECRET, $stored, 'EXIF/GPS and comments must be stripped.');
        $this->assertStringStartsWith("\xFF\xD8", $stored);

        // The orientation survives so portrait phone photos stay upright.
        $exif = @exif_read_data(Storage::disk('public')->path($path));
        $this->assertSame(6, $exif['Orientation'] ?? null);
        $this->assertArrayNotHasKey('GPSLatitudeRef', $exif ?: []);
    }

    public function test_extension_comes_from_the_content_not_the_client_name(): void
    {
        $user = User::factory()->parent()->create();

        $this->actingAs($user)->post(route('profile.photo.upload'), [
            'photo' => $this->uploadedFile($this->pngBytes(), 'evil.html'),
        ])->assertSessionHasNoErrors();

        $path = $user->refresh()->profile_photo_path;
        $this->assertStringEndsWith('.png', $path);
        $this->assertStringNotContainsString('evil', $path);
        $this->assertStringNotContainsString(self::SECRET, Storage::disk('public')->get($path));

        // A PHP file name is refused outright, even when the content is a real image.
        $this->actingAs($user)->post(route('profile.photo.upload'), [
            'photo' => $this->uploadedFile($this->pngBytes(), 'evil.php'),
        ])->assertSessionHasErrors('photo');
        $this->assertSame($path, $user->refresh()->profile_photo_path);
    }

    public function test_replacing_a_photo_deletes_the_old_file(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)->post(route('profile.photo.upload'), ['photo' => $this->uploadedFile($this->jpegBytes(), 'a.jpg')]);
        $first = $user->refresh()->profile_photo_path;

        $this->actingAs($user)->post(route('profile.photo.upload'), ['photo' => $this->uploadedFile($this->pngBytes(), 'b.png')]);
        $second = $user->refresh()->profile_photo_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_non_images_and_disallowed_types_are_rejected(): void
    {
        $user = User::factory()->student()->create();

        $fakeJpeg = $this->uploadedFile("<?php echo 'pwned'; ?>", 'foto.jpg');
        $this->actingAs($user)->from('/profile')->post(route('profile.photo.upload'), ['photo' => $fakeJpeg])
            ->assertSessionHasErrors('photo')->assertRedirect('/profile');

        $gif = $this->uploadedFile("GIF89a\x64\x00\x50\x00\x80\x00\x00<script>alert(1)</script>", 'kucing.gif');
        $this->actingAs($user)->from('/profile')->post(route('profile.photo.upload'), ['photo' => $gif])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->refresh()->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_too_small_images_are_rejected(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)->from('/profile')->post(route('profile.photo.upload'), [
            'photo' => $this->uploadedFile($this->jpegBytes(20, 20), 'kecil.jpg'),
        ])->assertSessionHasErrors('photo');
    }

    public function test_photo_can_be_deleted(): void
    {
        $user = User::factory()->student()->create();
        $this->actingAs($user)->post(route('profile.photo.upload'), ['photo' => $this->uploadedFile($this->jpegBytes(), 'a.jpg')]);
        $path = $user->refresh()->profile_photo_path;

        $this->actingAs($user)->delete(route('profile.photo.delete'))->assertRedirect(route('profile.edit').'#foto');

        $this->assertNull($user->refresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_json_callers_get_a_json_answer(): void
    {
        $user = User::factory()->student()->create();

        $this->actingAs($user)->postJson(route('profile.photo.upload'), [
            'photo' => $this->uploadedFile($this->pngBytes(), 'avatar.png'),
        ])->assertOk()->assertJson(['success' => true])->assertJsonStructure(['photo_url', 'message']);

        $this->actingAs($user)->deleteJson(route('profile.photo.delete'))->assertOk()->assertJson(['success' => true]);
    }

    public function test_guests_cannot_upload(): void
    {
        $this->post(route('profile.photo.upload'), ['photo' => $this->uploadedFile($this->pngBytes(), 'a.png')])
            ->assertRedirect(route('login'));

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
