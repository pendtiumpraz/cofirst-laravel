{{-- Identity summary at the top of the profile. Variables: $user, $roles (labels), $memberSince. --}}
<x-ui.card>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <x-ui.avatar :user="$user" size="xl" decorative />

        <div class="min-w-0 flex-1">
            <p class="section-title">{{ $user->name }}</p>
            <p class="mt-0.5 break-all text-sm text-fg-muted">{{ $user->email }}</p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                @foreach ($roles as $role)
                    <x-ui.badge outline>{{ $role }}</x-ui.badge>
                @endforeach
                @if ($memberSince)
                    <span class="text-xs text-fg-subtle">Bergabung sejak {{ $memberSince->translatedFormat('j F Y') }}</span>
                @endif
            </div>
        </div>
    </div>
</x-ui.card>
