{{--
    "Ganti peran" for users holding more than one dashboard role.
    Each option posts to role.set (redirects to the new role's dashboard).
    Variables: $roles (Navigation::rolesOf), $role (active role).
--}}
@php
    $switchRoute = Route::has('role.set') ? 'role.set' : 'role.switch';
@endphp
<p class="px-2.5 pb-1 pt-1 text-xs font-medium text-fg-subtle">Ganti peran</p>
<ul role="list">
    @foreach ($roles as $option)
        <li>
            <form method="POST" action="{{ route($switchRoute) }}">
                @csrf
                <input type="hidden" name="role" value="{{ $option }}">
                <button type="submit" @disabled($option === $role) aria-pressed="{{ $option === $role ? 'true' : 'false' }}"
                        class="flex min-h-10 w-full items-center gap-3 rounded-md px-2.5 text-left text-sm text-fg-muted hover:bg-surface-2 hover:text-fg disabled:cursor-default disabled:font-semibold disabled:text-fg disabled:hover:bg-transparent">
                    <x-heroicon-o-arrows-right-left class="size-5 shrink-0 text-fg-subtle" aria-hidden="true" />
                    <span class="flex-1">{{ \App\Support\Navigation\Navigation::ROLE_LABELS[$option] ?? ucfirst($option) }}</span>
                    @if ($option === $role)
                        <x-heroicon-m-check class="size-4 shrink-0 text-brand-text" aria-hidden="true" />
                        <span class="sr-only">(aktif)</span>
                    @endif
                </button>
            </form>
        </li>
    @endforeach
</ul>
