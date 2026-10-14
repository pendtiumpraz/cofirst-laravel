{{--
    Logout form. data-cf-logout makes the shell script post 'CF_LOGOUT' to the service
    worker first, so every cache on this (possibly shared) device is cleared.
--}}
<form method="POST" action="{{ route('logout') }}" data-cf-logout>
    @csrf
    <button type="submit" class="flex min-h-10 w-full items-center gap-3 rounded-md px-2.5 text-left text-sm font-medium text-danger hover:bg-danger-soft">
        <x-heroicon-o-arrow-right-start-on-rectangle class="size-5 shrink-0" aria-hidden="true" />
        Keluar
    </button>
</form>
