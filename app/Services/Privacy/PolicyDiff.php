<?php

namespace App\Services\Privacy;

/**
 * Line diff between two policy versions (admin "Bandingkan versi").
 *
 * Common prefix and suffix are trimmed first; the changed middle is diffed with a classic
 * LCS table, bounded in size so a full rewrite never exhausts memory.
 */
final class PolicyDiff
{
    /** Largest LCS table (lines × lines) computed before falling back to replace-all. */
    private const MAX_CELLS = 250000;

    /**
     * Diff two Markdown bodies line by line.
     *
     * Args:
     *     old (string): Previous body.
     *     new (string): New body.
     *
     * Returns:
     *     array<int, array{type: string, text: string}>: type same|add|del, in document order.
     */
    public static function lines(string $old, string $new): array
    {
        $a = preg_split('/\R/', rtrim($old));
        $b = preg_split('/\R/', rtrim($new));

        $start = 0;
        while ($start < count($a) && $start < count($b) && $a[$start] === $b[$start]) {
            $start++;
        }

        $endA = count($a) - 1;
        $endB = count($b) - 1;
        while ($endA >= $start && $endB >= $start && $a[$endA] === $b[$endB]) {
            $endA--;
            $endB--;
        }

        $ops = array_map(fn ($l) => ['type' => 'same', 'text' => $l], array_slice($a, 0, $start));
        array_push($ops, ...self::lcs(array_slice($a, $start, $endA - $start + 1), array_slice($b, $start, $endB - $start + 1)));

        foreach (array_slice($a, $endA + 1) as $line) {
            $ops[] = ['type' => 'same', 'text' => $line];
        }

        return $ops;
    }

    /**
     * Group a diff into display hunks: changed lines with `context` unchanged lines around
     * them; longer unchanged runs collapse into a 'skip' entry.
     *
     * Args:
     *     ops (array): Output of lines().
     *     context (int): Unchanged lines kept around each change.
     *
     * Returns:
     *     array<int, array{type: string, text?: string, count?: int}>
     */
    public static function fold(array $ops, int $context = 3): array
    {
        $keep = [];
        foreach ($ops as $i => $op) {
            if ($op['type'] !== 'same') {
                for ($k = max(0, $i - $context); $k <= min(count($ops) - 1, $i + $context); $k++) {
                    $keep[$k] = true;
                }
            }
        }

        $out = [];
        $skipped = 0;
        foreach ($ops as $i => $op) {
            if (isset($keep[$i])) {
                if ($skipped > 0) {
                    $out[] = ['type' => 'skip', 'count' => $skipped];
                    $skipped = 0;
                }
                $out[] = $op;
            } else {
                $skipped++;
            }
        }

        if ($skipped > 0) {
            $out[] = ['type' => 'skip', 'count' => $skipped];
        }

        return $out;
    }

    /**
     * Count added and removed lines.
     *
     * Returns:
     *     array{add: int, del: int}
     */
    public static function stats(array $ops): array
    {
        $types = array_count_values(array_column($ops, 'type'));

        return ['add' => $types['add'] ?? 0, 'del' => $types['del'] ?? 0];
    }

    /** LCS diff of the changed middle part. */
    private static function lcs(array $a, array $b): array
    {
        $n = count($a);
        $m = count($b);

        // Reason: keep memory bounded for very large rewrites; show them as replace-all.
        if ($n * $m > self::MAX_CELLS) {
            return array_merge(
                array_map(fn ($l) => ['type' => 'del', 'text' => $l], $a),
                array_map(fn ($l) => ['type' => 'add', 'text' => $l], $b),
            );
        }

        $len = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $len[$i][$j] = $a[$i] === $b[$j] ? $len[$i + 1][$j + 1] + 1 : max($len[$i + 1][$j], $len[$i][$j + 1]);
            }
        }

        $ops = [];
        $i = $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $ops[] = ['type' => 'same', 'text' => $a[$i++]];
                $j++;
            } elseif ($len[$i + 1][$j] >= $len[$i][$j + 1]) {
                $ops[] = ['type' => 'del', 'text' => $a[$i++]];
            } else {
                $ops[] = ['type' => 'add', 'text' => $b[$j++]];
            }
        }

        while ($i < $n) {
            $ops[] = ['type' => 'del', 'text' => $a[$i++]];
        }

        while ($j < $m) {
            $ops[] = ['type' => 'add', 'text' => $b[$j++]];
        }

        return $ops;
    }
}
