<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Auth pages shell: <x-guest-layout title="Masuk">…form…</x-guest-layout>.
 * Renders the 'split' or 'centered' variant chosen by setting('appearance.auth_layout').
 */
class GuestLayout extends Component
{
    /**
     * Args:
     *     title (string|null): Page title for <title>.
     *     heading (string|null): Optional heading shown above the slot.
     *     subheading (string|null): Optional supporting line under the heading.
     */
    public function __construct(
        public ?string $title = null,
        public ?string $heading = null,
        public ?string $subheading = null,
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
