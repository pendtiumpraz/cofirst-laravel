{{--
    Avatar disclosure menu: identity, Profil / Preferensi / Privasi & Data Saya, colour mode,
    Ganti peran (multi-role users), Pasang aplikasi (when installable) and Keluar.
    Variables: $user, $role, $roles, $account, $ui, $menuId (unique per instance), $showName (bool).
--}}
@php
    $menuId ??= 'cf-user-menu';
    $showName ??= false;
    $roleLabel = \App\Support\Navigation\Navigation::ROLE_LABELS[$role] ?? null;
@endphp
<div class="relative" x-data="cfMenu" @keydown.escape.stop="close()" @click.outside="close(false)">
    <button type="button" x-ref="button" @click="toggle()" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="{{ $menuId }}" aria-label="Menu akun {{ $user->name }}"
            class="flex min-h-11 items-center gap-2 rounded-full p-1 text-sm text-fg-muted transition-colors hover:bg-surface-2 hover:text-fg {{ $showName ? 'xl:pr-3' : '' }}">
        @include('layouts.shell.avatar', ['user' => $user, 'size' => 'sm'])
        @if ($showName)
            <span class="hidden max-w-[10rem] truncate font-medium text-fg xl:block">{{ $user->name }}</span>
        @endif
    </button>

    <div id="{{ $menuId }}" x-ref="panel" x-show="open" x-cloak
         x-transition:enter="transition duration-150 ease-out motion-reduce:transition-none" x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:leave="transition duration-100 ease-in motion-reduce:transition-none" x-transition:leave-end="opacity-0"
         class="absolute right-0 top-full z-50 mt-2 w-[min(20rem,calc(100vw-2rem))] rounded-lg border border-line-strong bg-surface p-1.5 text-sm shadow-2">
        <div class="flex items-center gap-3 px-2.5 pb-3 pt-2">
            @include('layouts.shell.avatar', ['user' => $user, 'size' => 'md'])
            <div class="min-w-0">
                <p class="truncate font-semibold text-fg">{{ $user->name }}</p>
                <p class="truncate text-xs text-fg-subtle">{{ $user->email }}</p>
                @if ($roleLabel)
                    <span class="badge badge-neutral badge-outline mt-1.5">{{ $roleLabel }}</span>
                @endif
            </div>
        </div>

        <ul role="list" class="border-t border-line py-1.5">
            @foreach ($account as $item)
                <li>
                    <a href="{{ $item['url'] }}" @if ($item['is_active']) aria-current="page" @endif
                       class="flex min-h-10 items-center gap-3 rounded-md px-2.5 text-fg-muted hover:bg-surface-2 hover:text-fg">
                        @svg('heroicon-o-'.$item['icon'], 'size-5 shrink-0 text-fg-subtle', ['aria-hidden' => 'true'])
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li x-data="{ installable: !!window.cfInstallEvent }" x-show="installable" x-cloak @cf-installable.window="installable = true" @cf-installed.window="installable = false">
                <button type="button" @click="window.cfInstallEvent && window.cfInstallEvent.prompt(); close()"
                        class="flex min-h-10 w-full items-center gap-3 rounded-md px-2.5 text-left text-fg-muted hover:bg-surface-2 hover:text-fg">
                    <x-heroicon-o-device-phone-mobile class="size-5 shrink-0 text-fg-subtle" aria-hidden="true" />
                    Pasang aplikasi
                </button>
            </li>
        </ul>

        @if ($ui['allow_color_mode'])
            <div class="border-t border-line px-2.5 py-2.5">
                <p class="mb-2 text-xs font-medium text-fg-subtle" id="{{ $menuId }}-mode">Tampilan</p>
                @include('layouts.shell.color-mode-options', ['labelledBy' => $menuId.'-mode'])
            </div>
        @endif

        @if (count($roles) > 1)
            <div class="border-t border-line py-1.5">
                @include('layouts.shell.role-switcher', ['roles' => $roles, 'role' => $role])
            </div>
        @endif

        <div class="border-t border-line pt-1.5">
            @include('layouts.shell.logout')
        </div>
    </div>
</div>
