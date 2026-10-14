<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Public website shell: <x-public-layout title="…" description="…">…</x-public-layout>.
 * Sticky header, rich footer with legal links, cookie banner and optional WhatsApp button.
 */
class PublicLayout extends Component
{
    /**
     * Args:
     *     title (string|null): Page title for <title> and Open Graph.
     *     description (string|null): Meta description (default setting('general.description')).
     *     transparentHeader (bool): Start the header without its hairline (hero pages).
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public bool $transparentHeader = false,
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.public');
    }
}
