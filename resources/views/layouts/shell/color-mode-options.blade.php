{{--
    Terang / Gelap / Sistem control for the shell menus: <x-ui.color-mode-toggle/> stretched to
    the menu width. Bound to Alpine.store('colorMode') (resources/js/color-mode.js), which applies
    the mode, stores it on this device and POSTs it to data-color-mode-endpoint when present.
--}}
<x-ui.color-mode-toggle class="flex w-full [&>.segmented-item]:flex-1 [&>.segmented-item]:justify-center" />
