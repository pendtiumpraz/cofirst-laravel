<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Dashboard shell: <x-app-layout title="…"> with optional <x-slot name="header">.
 *
 * The same view (layouts.app) also serves @extends('layouts.app') pages that use
 * @section('title'), @section('header') and @section('content').
 */
class AppLayout extends Component
{
    /**
     * Args:
     *     title (string|null): Page title for <title>, the mobile app bar and breadcrumbs.
     */
    public function __construct(public ?string $title = null)
    {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
