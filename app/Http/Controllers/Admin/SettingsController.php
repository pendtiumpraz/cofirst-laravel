<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Settings\AppearancePreview;
use App\Support\Settings\Requests\UpdateSettingsRequest;
use App\Support\Settings\SettingsAudit;
use App\Support\Settings\SettingsForm;
use App\Support\Settings\SettingsManager;
use App\Support\Settings\SettingsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin › Pengaturan: every group and field of config/settings.php rendered
 * automatically, with per-field/per-group reset, image uploads, an audit trail
 * and session-only "Pratinjau" of the A/B appearance choices.
 */
class SettingsController extends Controller
{
    public function __construct(
        private SettingsManager $settings,
        private SettingsRegistry $registry,
        private SettingsForm $form,
    ) {}

    /**
     * Open the first settings group.
     */
    public function index(): RedirectResponse
    {
        Gate::authorize('settings.view');

        return redirect()->route('admin.settings.edit', array_key_first($this->registry->groups()));
    }

    /**
     * Show one settings group.
     */
    public function edit(Request $request, string $group): View
    {
        Gate::authorize('settings.view');

        $fields = $this->registry->fields($group);
        $records = $this->settings->records(array_keys($fields));
        $user = $request->user();

        return view('admin.settings.edit', [
            'groups' => collect($this->registry->groups())
                ->map(fn (array $meta) => $meta + ['editable' => $this->registry->canEdit($user, $meta['key'])])
                ->all(),
            'group' => $this->registry->group($group),
            'sections' => $this->registry->sections($group),
            'values' => collect($fields)->mapWithKeys(fn ($field, $key) => [$key => $this->settings->saved($key)])->all(),
            'records' => $records,
            'lastChange' => $records->sortByDesc('updated_at')->first(),
            'canEdit' => $this->registry->canEdit($user, $group),
            'preview' => AppearancePreview::current(),
        ]);
    }

    /**
     * Save one settings group.
     */
    public function update(UpdateSettingsRequest $request, string $group): RedirectResponse
    {
        $changed = $this->form->save($group, $request, $request->user());
        $label = $this->registry->group($group)['label'];

        if ($changed === []) {
            return redirect()->route('admin.settings.edit', $group)
                ->with('info', 'Tidak ada perubahan untuk disimpan.');
        }

        SettingsAudit::record('updated', $group, $changed, $request->user());

        return redirect()->route('admin.settings.edit', $group)->with('success', [
            'title' => 'Pengaturan disimpan',
            'message' => 'Perubahan pada '.$label.' sudah berlaku ('.count($changed).' bidang).',
        ]);
    }

    /**
     * Reset one field (input "field") or the whole group to the configured defaults.
     */
    public function reset(Request $request, string $group): RedirectResponse
    {
        Gate::authorize('settings.update', $group);

        $validated = $request->validate([
            'field' => ['nullable', 'string', Rule::in(array_keys($this->registry->fields($group)))],
        ], [], ['field' => 'bidang']);

        $field = $validated['field'] ?? null;
        $reset = $this->form->reset($group, $field);

        if ($reset === []) {
            return redirect()->route('admin.settings.edit', $group)
                ->with('info', 'Semua nilai sudah memakai bawaan.');
        }

        SettingsAudit::record('reset', $group, $reset, $request->user());

        $message = $field !== null
            ? '"'.$this->registry->field($field)['label'].'" dikembalikan ke nilai bawaan.'
            : count($reset).' bidang di '.$this->registry->group($group)['label'].' dikembalikan ke nilai bawaan.';

        return redirect()->route('admin.settings.edit', $group)->with('success', $message);
    }

    /**
     * Start a session-only preview of one appearance option (signed "Pratinjau" link).
     */
    public function preview(Request $request): RedirectResponse
    {
        Gate::authorize('settings.view');

        $key = (string) $request->query('key');
        $value = (string) $request->query('value');

        abort_unless(AppearancePreview::supports($key) && AppearancePreview::isValidOption($key, $value), 404);

        $target = AppearancePreview::targetUrl($key);
        abort_if($target === null, 404);

        AppearancePreview::start($key, $value);
        $option = $this->registry->field($key)['options'][$value]['label'] ?? $value;

        return redirect()->to($target)->with('info', [
            'title' => 'Mode pratinjau: '.$option,
            'message' => 'Hanya Anda yang melihat tampilan ini. Terapkan atau akhiri pratinjau di Pengaturan › Tampilan & Versi.',
        ]);
    }

    /**
     * A sample sign-in page rendered with the (previewed) auth layout — signed-in admins cannot open /login.
     */
    public function previewAuth(): View
    {
        Gate::authorize('settings.view');

        return view('admin.settings.preview-auth', [
            'layout' => setting('appearance.auth_layout'),
            'option' => $this->registry->field('appearance.auth_layout')['options'][setting('appearance.auth_layout')]['label'] ?? null,
        ]);
    }

    /**
     * Save every option that is being previewed, then end the preview.
     */
    public function applyPreview(Request $request): RedirectResponse
    {
        Gate::authorize('settings.update', 'appearance');

        $values = AppearancePreview::current();
        AppearancePreview::clear();

        if ($values !== []) {
            $this->settings->setMany($values, $request->user()?->getKey());
            SettingsAudit::record('preview_applied', 'appearance', array_keys($values), $request->user());
        }

        return redirect()->route('admin.settings.edit', 'appearance')->with('success', [
            'title' => 'Pratinjau diterapkan',
            'message' => 'Pilihan tampilan kini berlaku untuk semua pengguna.',
        ]);
    }

    /**
     * End the preview without saving anything.
     */
    public function endPreview(): RedirectResponse
    {
        Gate::authorize('settings.view');

        AppearancePreview::clear();

        return redirect()->route('admin.settings.edit', 'appearance')
            ->with('success', 'Pratinjau diakhiri. Tampilan kembali ke pengaturan tersimpan.');
    }
}
