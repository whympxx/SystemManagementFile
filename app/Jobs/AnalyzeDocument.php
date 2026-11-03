<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\AiClassificationException;
use App\Services\AiClassifier;
use App\Services\KeywordClassifier;
use App\Services\TextExtractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Membaca isi berkas lalu menentukan kategori, tag, judul, ringkasan, dan tanggal penting.
 * Urutan: ekstraksi teks -> auto-kategori kata kunci -> (opsional) AI Claude menimpa hasilnya.
 */
class AnalyzeDocument implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /**
     * @param  bool  $keepCategory  true jika user sudah memilih kategori sendiri saat upload
     */
    public function __construct(public Document $document, public bool $keepCategory = false) {}

    public function handle(TextExtractor $extractor, KeywordClassifier $keywords, AiClassifier $ai): void
    {
        $doc = $this->document;
        $path = Storage::disk('local')->path($doc->path);
        $categories = $doc->user->categories()->get();
        $defaultTitle = $doc->title === pathinfo($doc->original_name, PATHINFO_FILENAME);

        $doc->content_text = $extractor->extract($path, $doc->extension);

        $result = $keywords->classify($categories, $doc->original_name, $doc->content_text);
        if (! $this->keepCategory && $result['category']) {
            $doc->category_id = $result['category']->id;
            $doc->classified_by = 'keyword';
        }
        $tags = $result['tags'];
        $doc->analysis_status = 'done';
        $doc->analysis_error = null;

        if (AiClassifier::enabled()) {
            try {
                $data = $ai->classify($categories, $doc->original_name, $doc->extension, $doc->content_text, $path);

                if (! $this->keepCategory && ($category = $categories->firstWhere('name', $data['category']))) {
                    $doc->category_id = $category->id;
                    $doc->classified_by = 'ai';
                }
                if ($defaultTitle && filled($data['title'])) {
                    $doc->title = mb_substr($data['title'], 0, 200);
                }
                $doc->ai_summary = $data['summary'] ?: null;
                $doc->document_date ??= $this->parseDate($data['document_date']);
                $doc->expires_at ??= $this->parseDate($data['expires_at']);
                $tags = array_merge($data['tags'], $tags);
            } catch (AiClassificationException $e) {
                // Hasil kata kunci tetap dipakai; catat alasan AI gagal
                $doc->analysis_error = $e->getMessage();
            }
        }

        $doc->save();
        $this->attachTags($doc, $tags);
    }

    public function failed(\Throwable $e): void
    {
        $this->document->update(['analysis_status' => 'failed', 'analysis_error' => mb_substr($e->getMessage(), 0, 500)]);
    }

    private function attachTags(Document $doc, array $names): void
    {
        $ids = collect($names)
            ->map(fn ($t) => mb_substr(trim(mb_strtolower((string) $t), " #\t\n"), 0, 50))
            ->filter()
            ->unique()
            ->take(8)
            ->map(fn ($name) => $doc->user->tags()->firstOrCreate(['name' => $name])->id);

        $doc->tags()->syncWithoutDetaching($ids->all());
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
