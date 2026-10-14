{{--
    Flash messages and the validation summary as toasts (<x-ui.toast/>): top-centre on mobile,
    bottom-right on desktop; success/info/warning auto-hide after 5 s, errors stay. Reads the
    session keys success | error | warning | info | message | status (Breeze codes translated)
    and hosts client toasts from CF.toast / Alpine.store('toast'). Include once per layout.
--}}
<x-ui.toast />
