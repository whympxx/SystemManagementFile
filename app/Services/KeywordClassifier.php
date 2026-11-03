<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Auto-kategori tanpa AI: mencocokkan kata kunci kategori dengan nama file dan isi dokumen.
 * Kata kunci di nama file diberi bobot lebih tinggi daripada di isi.
 */
class KeywordClassifier
{
    /**
     * @param  Collection<int, Category>  $categories
     * @return array{category: ?Category, tags: list<string>}
     */
    public function classify(Collection $categories, string $filename, ?string $text): array
    {
        $name = $this->normalize(pathinfo($filename, PATHINFO_FILENAME));
        $body = $this->normalize(mb_substr((string) $text, 0, 20_000));

        $best = null;
        $bestScore = 0;
        $matched = [];

        foreach ($categories as $category) {
            $score = 0;
            foreach ($category->keywordList() as $keyword) {
                $pattern = '/\b'.preg_quote($keyword, '/').'\b/u';
                $inName = preg_match($pattern, $name);
                $inBody = $body !== '' ? min(preg_match_all($pattern, $body), 5) : 0;
                if ($inName || $inBody) {
                    $score += $inName * 5 + $inBody;
                    $matched[$keyword] = ($matched[$keyword] ?? 0) + $inName * 5 + $inBody;
                }
            }
            if ($score > $bestScore) {
                $best = $category;
                $bestScore = $score;
            }
        }

        $best ??= $categories->firstWhere('is_fallback', true);

        arsort($matched);
        $tags = array_slice(array_keys($matched), 0, 4);

        if (preg_match('/\b(20\d{2})\b/', $name.' '.mb_substr($body, 0, 3000), $m)) {
            $tags[] = $m[1];
        }

        return ['category' => $best, 'tags' => array_values(array_unique($tags))];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);

        return preg_replace('/[_\-\.]+/u', ' ', $value);
    }
}
