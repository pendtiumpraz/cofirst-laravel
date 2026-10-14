<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The signed-in devices of a user (rows of the `sessions` table when the
 * session driver is "database") and ending them.
 */
class UserSessionService
{
    /**
     * Whether sessions are stored in the database (and can therefore be listed).
     */
    public function supported(): bool
    {
        return config('session.driver') === 'database';
    }

    /**
     * The user's sessions, most recent first, described for display.
     *
     * Args:
     *     user (User): Session owner.
     *     currentId (string|null): Id of the session making the request.
     *
     * Returns:
     *     Collection<int, object>: {id, is_current, ip_address, browser, platform, device, icon, last_active_at}
     */
    public function forUser(User $user, ?string $currentId): Collection
    {
        if (! $this->supported()) {
            return collect();
        }

        $timezone = function_exists('setting') ? (string) setting('general.timezone', 'Asia/Jakarta') : 'Asia/Jakarta';

        return $this->table()
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(function (object $row) use ($currentId, $timezone) {
                $agent = self::describeAgent($row->user_agent);

                return (object) [
                    'id' => $row->id,
                    'is_current' => $currentId !== null && hash_equals((string) $row->id, $currentId),
                    'ip_address' => $row->ip_address,
                    'browser' => $agent['browser'],
                    'platform' => $agent['platform'],
                    'device' => $agent['device'],
                    'icon' => $agent['icon'],
                    'last_active_at' => Carbon::createFromTimestamp((int) $row->last_activity)->setTimezone($timezone)->locale('id'),
                ];
            })
            // Reason: show the device in use first, whatever its last_activity says.
            ->sortByDesc('is_current')
            ->values();
    }

    /**
     * Sign the user out of every other device.
     *
     * Auth::logoutOtherDevices() re-hashes the password, which invalidates the
     * "remember me" cookies of other devices; deleting the other session rows ends
     * their current sessions immediately.
     *
     * Returns:
     *     int: Number of other sessions ended.
     */
    public function logoutOtherDevices(Request $request, string $password): int
    {
        Auth::guard('web')->logoutOtherDevices($password);

        return $this->deleteOthers($request->user(), $request->session()->getId());
    }

    /**
     * Delete every session row of the user except $keepId.
     */
    public function deleteOthers(User $user, ?string $keepId): int
    {
        if (! $this->supported()) {
            return 0;
        }

        return $this->table()
            ->where('user_id', $user->getKey())
            ->when($keepId, fn (Builder $query) => $query->where('id', '!=', $keepId))
            ->delete();
    }

    /**
     * Describe a user agent string as browser, platform and device type.
     *
     * Returns:
     *     array{browser:string, platform:string, device:string, icon:string}
     */
    public static function describeAgent(?string $agent): array
    {
        $agent = (string) $agent;

        $device = match (true) {
            (bool) preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $agent) => 'tablet',
            (bool) preg_match('/Mobile|iPhone|iPod|Android|Windows Phone/i', $agent) => 'mobile',
            default => 'desktop',
        };

        $platform = match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/i', $agent) => 'iOS',
            (bool) preg_match('/Android/i', $agent) => 'Android',
            (bool) preg_match('/CrOS/i', $agent) => 'ChromeOS',
            (bool) preg_match('/Windows/i', $agent) => 'Windows',
            (bool) preg_match('/Macintosh|Mac OS X/i', $agent) => 'macOS',
            (bool) preg_match('/Linux/i', $agent) => 'Linux',
            default => 'sistem tidak dikenal',
        };

        $browser = match (true) {
            (bool) preg_match('/Edg(e|A|iOS)?\//', $agent) => 'Microsoft Edge',
            (bool) preg_match('/OPR\/|Opera/', $agent) => 'Opera',
            (bool) preg_match('/SamsungBrowser/', $agent) => 'Samsung Internet',
            (bool) preg_match('/Firefox\/|FxiOS/', $agent) => 'Firefox',
            (bool) preg_match('/Chrome\/|CriOS/', $agent) => 'Chrome',
            (bool) preg_match('/Safari\//', $agent) && Str::contains($agent, 'Version/') => 'Safari',
            default => 'Peramban tidak dikenal',
        };

        $icon = match ($device) {
            'mobile' => 'device-phone-mobile',
            'tablet' => 'device-tablet',
            default => 'computer-desktop',
        };

        return compact('browser', 'platform', 'device', 'icon');
    }

    /**
     * Query builder for the sessions table on the session connection.
     */
    private function table(): Builder
    {
        return DB::connection(config('session.connection'))->table(config('session.table', 'sessions'));
    }
}
