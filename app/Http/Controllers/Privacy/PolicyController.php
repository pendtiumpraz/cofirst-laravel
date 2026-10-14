<?php

namespace App\Http\Controllers\Privacy;

use App\Http\Controllers\Controller;
use App\Models\Privacy\PolicyDocument;
use App\Services\Privacy\PolicyService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public policy pages (UU 27/2022 Pasal 5 & 21; PP 33/2026 Pasal 61-62): the privacy notice,
 * terms, cookie policy and children's notice in force, plus the version history so anyone
 * can see what changed and when (Pasal 21 ayat (2)).
 */
class PolicyController extends Controller
{
    /** Types with a public page (the DPA template is internal). */
    private const PUBLIC_TYPES = ['privacy_notice', 'terms', 'cookie_policy', 'children_notice'];

    public function __construct(private readonly PolicyService $policies)
    {
    }

    /** Kebijakan Privasi. */
    public function privacy(): View
    {
        return $this->page('privacy_notice');
    }

    /** Syarat & Ketentuan. */
    public function terms(): View
    {
        return $this->page('terms');
    }

    /** Kebijakan Cookie. */
    public function cookies(): View
    {
        return $this->page('cookie_policy');
    }

    /** Pemberitahuan privasi untuk anak & orang tua. */
    public function children(): View
    {
        return $this->page('children_notice');
    }

    /**
     * Published versions of one document type (?jenis=privacy_notice|terms|cookie_policy|children_notice).
     */
    public function history(Request $request): View
    {
        $type = in_array($request->query('jenis'), self::PUBLIC_TYPES, true) ? $request->query('jenis') : 'privacy_notice';

        return view('privacy.policy-history', [
            'type' => $type,
            'types' => array_intersect_key(PolicyDocument::TYPES, array_flip(self::PUBLIC_TYPES)),
            'versions' => $this->policies->history($type),
            'current' => $this->policies->currentMeta($type),
        ]);
    }

    /**
     * One published version (current, scheduled or superseded).
     */
    public function version(PolicyDocument $policy): View
    {
        abort_unless($policy->isPublished() && in_array($policy->type, self::PUBLIC_TYPES, true), 404);

        return $this->render($policy);
    }

    /** Current version of a type (bundled default text when nothing is published yet). */
    private function page(string $type): View
    {
        $document = $this->policies->currentOrDefault($type);

        abort_unless($document !== null, 404);

        return $this->render($document);
    }

    /** Shared policy page. */
    private function render(PolicyDocument $document): View
    {
        $current = $this->policies->currentMeta($document->type);
        $html = $this->policies->render($document);

        return view('privacy.policy', [
            'document' => $document,
            'html' => $html,
            'toc' => $this->policies->toc($html),
            'upcoming' => $document->isScheduled() ? null : $this->policies->upcoming($document->type),
            'isCurrent' => ! $document->exists || ($current && $current->id === $document->id),
            'currentUrl' => $document->publicUrl(),
            'dpoEmail' => (string) setting('privacy.dpo_email', ''),
        ]);
    }
}
