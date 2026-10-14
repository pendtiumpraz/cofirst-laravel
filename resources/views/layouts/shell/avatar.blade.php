{{--
    Avatar of the signed-in user in the shell menus: <x-ui.avatar> (photo, else initials on a
    stable tint). Decorative because the name is always printed or announced next to it.
    Variables: $user, $size ('sm' 32px | 'md' 40px).
--}}
<x-ui.avatar :user="$user" :size="$size ?? 'sm'" decorative />
