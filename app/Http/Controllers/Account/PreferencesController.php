<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Support\Navigation\Appearance;
use App\Support\Settings\Requests\UpdateColorModeRequest;
use App\Support\Settings\Requests\UpdatePreferencesRequest;
use App\Support\Settings\UserPreferences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Akun › Preferensi: the signed-in user's own appearance, language and email
 * notification choices, stored in users.preferences (JSON). Users can only ever
 * change their own preferences — there is no user parameter.
 */
class PreferencesController extends Controller
{
    /**
     * Show the preferences form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $state = Appearance::state($user);

        return view('account.preferences', [
            'user' => $user,
            'values' => [
                'theme' => $user->preference('theme', setting()->saved('appearance.theme')),
                'color_mode' => $user->preference('color_mode', $state['default_color_mode']),
                'density' => $user->preference('density', setting()->saved('appearance.density')),
                'text_size' => $user->preference('text_size', 'md'),
                'reduce_motion' => (bool) $user->preference('reduce_motion', false),
                'locale' => UserPreferences::locale($user) ?? setting()->saved('general.locale'),
            ],
            'allowTheme' => (bool) setting('appearance.allow_user_theme'),
            'allowColorMode' => (bool) setting('appearance.allow_user_color_mode'),
            'emailOptions' => UserPreferences::emailOptionsFor($user),
            'themeOptions' => config('settings.fields', [])['appearance.theme']['options'] ?? [],
        ]);
    }

    /**
     * Save the preferences (merged into users.preferences so other modules' keys survive).
     */
    public function update(UpdatePreferencesRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $preferences = is_array($user->preferences) ? $user->preferences : [];

        $preferences['locale'] = $data['locale'];
        $preferences['density'] = $data['density'];
        $preferences['text_size'] = $data['text_size'];
        $preferences['reduce_motion'] = (bool) $data['reduce_motion'];

        if (array_key_exists('color_mode', $data)) {
            $preferences['color_mode'] = $data['color_mode'];
        }
        if (array_key_exists('theme', $data)) {
            $preferences['theme'] = $data['theme'];
        }

        $email = is_array($preferences['notifications']['email'] ?? null) ? $preferences['notifications']['email'] : [];
        foreach (array_keys(UserPreferences::emailOptionsFor($user)) as $type) {
            // Reason: unchecked toggles post "0" through their hidden input; a missing key means "keep".
            $submitted = $data['notifications']['email'][$type] ?? null;
            if ($submitted !== null) {
                $email[$type] = (bool) $submitted;
            }
        }
        $preferences['notifications'] = array_merge(
            is_array($preferences['notifications'] ?? null) ? $preferences['notifications'] : [],
            ['email' => $email],
        );

        $user->forceFill(['preferences' => $preferences])->save();

        return redirect()->route('account.preferences')->with('success', [
            'title' => 'Preferensi disimpan',
            'message' => 'Tampilan dan notifikasi mengikuti pilihan Anda.',
        ]);
    }

    /**
     * Persist the colour mode picked from the shell menu (JSON endpoint for color-mode.js).
     */
    public function colorMode(UpdateColorModeRequest $request): JsonResponse
    {
        $user = $request->user();
        $preferences = is_array($user->preferences) ? $user->preferences : [];
        $preferences['color_mode'] = $request->validated('color_mode');

        $user->forceFill(['preferences' => $preferences])->save();

        return response()->json(['color_mode' => $preferences['color_mode']]);
    }
}
