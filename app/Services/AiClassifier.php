<?php

namespace App\Services;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Category;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Klasifikasi dan ringkasan dokumen memakai AI.
 *
 * Dua penyedia (AI_PROVIDER di .env):
 *  - "openai"    : API format OpenAI (BazaarLink, OpenRouter, dll.) lewat AI_BASE_URL / AI_API_KEY / AI_MODEL
 *  - "anthropic" : Claude lewat ANTHROPIC_API_KEY / ANTHROPIC_MODEL
 */
class AiClassifier
{
    /** Batas teks yang dikirim ke AI (cukup untuk mengenali jenis dokumen). */
    private const MAX_TEXT_CHARS = 60_000;

    private const MAX_PDF_BYTES = 20 * 1024 * 1024;

    private const MAX_IMAGE_BYTES = 5 * 1024 * 1024;

    private const SYSTEM_PROMPT = 'Kamu adalah asisten arsip kantor di Indonesia. Kamu membantu sekretaris dan karyawan '
        .'mengelompokkan berkas. Jawab dalam Bahasa Indonesia.';

    public function __construct(private ?Client $client = null) {}

    public static function provider(): string
    {
        return config('services.ai.provider') === 'anthropic' ? 'anthropic' : 'openai';
    }

    public static function enabled(): bool
    {
        return self::provider() === 'anthropic'
            ? filled(config('services.anthropic.key'))
            : filled(config('services.ai.key')) && filled(config('services.ai.base_url'));
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return array{category: string, title: string, summary: string, tags: list<string>, document_date: string, expires_at: string}
     *
     * @throws AiClassificationException
     */
    public function classify(Collection $categories, string $filename, string $extension, ?string $text, string $absolutePath): array
    {
        $names = $categories->pluck('name')->values()->all();
        $guide = $categories->map(fn (Category $c) => '- '.$c->name.($c->keywords ? ' (contoh: '.$c->keywords.')' : ''))->implode("\n");
        $instruction = "Nama file: {$filename}\n\nKategori yang tersedia:\n{$guide}\n\n"
            .'Tentukan kategori yang paling cocok, buat judul singkat yang jelas, ringkasan 2-3 kalimat, '
            .'dan 3-6 tag pendek (huruf kecil). Isi document_date dengan tanggal dokumen dan expires_at dengan '
            .'tanggal berakhir/jatuh tempo/masa berlaku jika tertulis, format YYYY-MM-DD; kosongkan ("") jika tidak ada.';

        $data = self::provider() === 'anthropic'
            ? $this->viaAnthropic($this->schema($names), $instruction, $extension, $text, $absolutePath)
            : $this->viaOpenAiCompatible($this->schema($names), $instruction, $text);

        return $this->normalize($data, $names);
    }

    private function schema(array $names): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'category' => ['type' => 'string', 'enum' => $names],
                'title' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
                'document_date' => ['type' => 'string'],
                'expires_at' => ['type' => 'string'],
            ],
            'required' => ['category', 'title', 'summary', 'tags', 'document_date', 'expires_at'],
            'additionalProperties' => false,
        ];
    }

    // ---------------------------------------------------------------- OpenAI-compatible (BazaarLink)

    private function viaOpenAiCompatible(array $schema, string $instruction, ?string $text): array
    {
        $messages = [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ['role' => 'user', 'content' => $this->textSection($text)."\n\n".$instruction
                ."\n\nBalas HANYA dengan satu objek JSON sesuai skema ini, tanpa teks lain:\n"
                .json_encode($schema, JSON_UNESCAPED_UNICODE)],
        ];

        $payload = [
            'model' => config('services.ai.model'),
            'messages' => $messages,
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_schema', 'json_schema' => ['name' => 'klasifikasi_dokumen', 'strict' => true, 'schema' => $schema]],
        ];

        $response = $this->post($payload);

        // Sebagian model tidak mendukung json_schema; ulangi dengan mode JSON biasa
        if ($response->status() === 400 && str_contains(strtolower($response->body()), 'response_format')) {
            $payload['response_format'] = ['type' => 'json_object'];
            $response = $this->post($payload);
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new AiClassificationException('API key AI tidak valid atau tidak punya akses ke model ini. '.$this->errorMessage($response));
        }
        if ($response->status() === 429) {
            throw new AiClassificationException('Batas pemakaian AI tercapai, coba lagi nanti.');
        }
        if ($response->failed()) {
            throw new AiClassificationException('AI menolak permintaan: '.$this->errorMessage($response));
        }

        $choice = $response->json('choices.0');
        if (($choice['finish_reason'] ?? null) === 'length') {
            throw new AiClassificationException('Jawaban AI terpotong.');
        }

        return $this->decodeJson((string) ($choice['message']['content'] ?? ''));
    }

    private function post(array $payload): Response
    {
        try {
            return Http::withToken(config('services.ai.key'))
                ->acceptJson()
                ->timeout(120)
                ->retry(2, 1500, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->post(rtrim(config('services.ai.base_url'), '/').'/chat/completions', $payload);
        } catch (ConnectionException $e) {
            throw new AiClassificationException('Tidak bisa terhubung ke layanan AI.', previous: $e);
        }
    }

    private function errorMessage(Response $response): string
    {
        return mb_substr((string) ($response->json('error.message') ?? $response->body()), 0, 300);
    }

    /** Ambil objek JSON dari jawaban model (buang blok <think> dan pagar ```json bila ada). */
    private function decodeJson(string $content): array
    {
        $content = trim(preg_replace('#<think>.*?</think>#s', '', $content));
        $content = preg_replace('#^```(?:json)?\s*|\s*```$#', '', $content);

        $data = json_decode($content, true);
        if (! is_array($data) && preg_match('/\{.*\}/s', $content, $m)) {
            $data = json_decode($m[0], true);
        }
        if (! is_array($data)) {
            throw new AiClassificationException('Jawaban AI tidak bisa dibaca.');
        }

        return $data;
    }

    // ---------------------------------------------------------------- Anthropic (Claude)

    private function viaAnthropic(array $schema, string $instruction, string $extension, ?string $text, string $path): array
    {
        $content = $this->claudeFileBlocks($extension, $text, $path);
        $content[] = ['type' => 'text', 'text' => $instruction];

        try {
            $message = $this->client()->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: 4000,
                system: self::SYSTEM_PROMPT,
                messages: [['role' => 'user', 'content' => $content]],
                outputConfig: ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => $schema]],
            );
        } catch (AuthenticationException $e) {
            throw new AiClassificationException('API key Anthropic tidak valid.', previous: $e);
        } catch (RateLimitException $e) {
            throw new AiClassificationException('Batas pemakaian AI tercapai, coba lagi nanti.', previous: $e);
        } catch (APIStatusException $e) {
            throw new AiClassificationException('AI menolak permintaan: '.$e->getMessage(), previous: $e);
        } catch (APIConnectionException $e) {
            throw new AiClassificationException('Tidak bisa terhubung ke layanan AI.', previous: $e);
        }

        if ($message->stopReason === 'refusal') {
            throw new AiClassificationException('AI tidak bersedia memproses dokumen ini.');
        }
        if ($message->stopReason === 'max_tokens') {
            throw new AiClassificationException('Jawaban AI terpotong.');
        }

        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                return $this->decodeJson($block->text);
            }
        }

        throw new AiClassificationException('Jawaban AI tidak bisa dibaca.');
    }

    /** Kirim teks hasil ekstraksi; untuk scan (PDF/gambar tanpa teks) kirim file aslinya agar dibaca Claude. */
    private function claudeFileBlocks(string $extension, ?string $text, string $path): array
    {
        if (filled($text)) {
            return [['type' => 'text', 'text' => $this->textSection($text)]];
        }

        $size = @filesize($path) ?: PHP_INT_MAX;

        if ($extension === 'pdf' && $size <= self::MAX_PDF_BYTES) {
            return [[
                'type' => 'document',
                'source' => ['type' => 'base64', 'mediaType' => 'application/pdf', 'data' => base64_encode(file_get_contents($path))],
            ]];
        }

        $imageTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        if (isset($imageTypes[$extension]) && $size <= self::MAX_IMAGE_BYTES) {
            return [[
                'type' => 'image',
                'source' => ['type' => 'base64', 'mediaType' => $imageTypes[$extension], 'data' => base64_encode(file_get_contents($path))],
            ]];
        }

        return [];
    }

    private function client(): Client
    {
        return $this->client ??= new Client(apiKey: config('services.anthropic.key'));
    }

    // ---------------------------------------------------------------- Bersama

    private function textSection(?string $text): string
    {
        if (blank($text)) {
            return '(Isi dokumen tidak bisa dibaca, misalnya hasil scan. Tebak dari nama file saja.)';
        }
        $truncated = mb_strlen($text) > self::MAX_TEXT_CHARS;

        return 'Isi dokumen'.($truncated ? ' (hanya bagian awal)' : '').":\n<dokumen>\n"
            .mb_substr($text, 0, self::MAX_TEXT_CHARS)."\n</dokumen>";
    }

    /** Pastikan semua kolom ada dan bertipe benar, apa pun model yang menjawab. */
    private function normalize(array $data, array $names): array
    {
        $category = (string) ($data['category'] ?? '');

        return [
            'category' => in_array($category, $names, true) ? $category : '',
            'title' => trim((string) ($data['title'] ?? '')),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'tags' => array_values(array_filter(array_map('strval', (array) ($data['tags'] ?? [])))),
            'document_date' => (string) ($data['document_date'] ?? ''),
            'expires_at' => (string) ($data['expires_at'] ?? ''),
        ];
    }
}
