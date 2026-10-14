<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Settings\Concerns\InteractsWithSettings;
use Tests\TestCase;

/**
 * Image fields (custom logos): stored on the public disk under settings/,
 * replaced and removed cleanly, SVG refused.
 */
class SettingsImageUploadTest extends TestCase
{
    use InteractsWithSettings;
    use RefreshDatabase;

    /** 1×1 transparent PNG (no GD extension needed). */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
        Storage::fake('public');
        $this->admin = $this->userWithRole('admin');
    }

    public function test_logo_is_stored_under_settings_on_the_public_disk(): void
    {
        $this->upload(['custom_logo' => $this->png()])->assertSessionHasNoErrors();

        $path = setting('appearance.custom_logo');
        $this->assertIsString($path);
        $this->assertStringStartsWith('settings/', $path);
        $this->assertStringEndsWith('.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_and_removing_a_logo_deletes_the_old_file(): void
    {
        $this->upload(['custom_logo' => $this->png()]);
        $first = setting('appearance.custom_logo');

        $this->upload(['custom_logo' => $this->png('baru.png')])->assertSessionHasNoErrors();
        $second = setting('appearance.custom_logo');
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->upload([], ['remove' => ['appearance' => ['custom_logo' => '1']]])->assertSessionHasNoErrors();
        $this->assertNull(setting('appearance.custom_logo'));
        Storage::disk('public')->assertMissing($second);
    }

    public function test_svg_and_non_images_are_refused(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        $this->upload(['custom_logo' => $svg])->assertSessionHasErrors('s.appearance.custom_logo');

        $this->assertNull(setting('appearance.custom_logo'));
        $this->assertSame([], Storage::disk('public')->allFiles('settings'));
    }

    public function test_resetting_the_field_deletes_the_file(): void
    {
        $this->upload(['custom_logo' => $this->png()]);
        $path = setting('appearance.custom_logo');

        $this->actingAs($this->admin)
            ->post(route('admin.settings.reset', 'appearance'), ['field' => 'appearance.custom_logo'])
            ->assertSessionHas('success');

        $this->assertNull(setting('appearance.custom_logo'));
        Storage::disk('public')->assertMissing($path);
    }

    private function png(string $name = 'logo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG));
    }

    /**
     * PUT the appearance group with the given image inputs and extra input.
     *
     * @param  array<string, mixed>  $files
     * @param  array<string, mixed>  $extra
     */
    private function upload(array $files = [], array $extra = []): TestResponse
    {
        $payload = array_replace_recursive($this->groupPayload('appearance'), ['s' => ['appearance' => $files]], $extra);

        return $this->actingAs($this->admin)->put(route('admin.settings.update', 'appearance'), $payload);
    }
}
