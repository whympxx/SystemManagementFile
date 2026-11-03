<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

/**
 * Mengambil teks dari berkas agar isinya bisa dicari dan dikategorikan.
 * Mendukung PDF (yang berisi teks, bukan hasil scan), Word, Excel, PowerPoint, dan teks biasa.
 */
class TextExtractor
{
    public const MAX_CHARS = 200_000;

    public function extract(string $absolutePath, string $extension): ?string
    {
        try {
            $text = match ($extension) {
                'pdf' => (new PdfParser)->parseFile($absolutePath)->getText(),
                'txt', 'md', 'csv' => file_get_contents($absolutePath),
                'docx' => $this->fromZipXml($absolutePath, ['word/document.xml']),
                'xlsx' => $this->fromZipXml($absolutePath, ['xl/sharedStrings.xml']),
                'pptx' => $this->fromZipXml($absolutePath, $this->pptxSlides($absolutePath)),
                'odt', 'ods', 'odp' => $this->fromZipXml($absolutePath, ['content.xml']),
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Ekstraksi teks gagal', ['path' => $absolutePath, 'error' => $e->getMessage()]);

            return null;
        }

        if ($text === null || $text === false) {
            return null;
        }

        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = trim(preg_replace('/[ \t]+/u', ' ', preg_replace("/\R{3,}/u", "\n\n", $text)));

        return $text === '' ? null : mb_substr($text, 0, self::MAX_CHARS);
    }

    /** @param list<string> $entries */
    private function fromZipXml(string $path, array $entries): ?string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return null;
        }

        $parts = [];
        foreach ($entries as $entry) {
            $xml = $zip->getFromName($entry);
            if ($xml !== false) {
                // Akhir paragraf/sel jadi baris baru, sisanya buang tag
                $xml = preg_replace('#</(w:p|a:p|text:p|si|row)>#', "\n", $xml);
                $parts[] = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }
        $zip->close();

        return implode("\n", $parts);
    }

    /** @return list<string> */
    private function pptxSlides(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return [];
        }
        $slides = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('#^ppt/slides/slide\d+\.xml$#', $name)) {
                $slides[] = $name;
            }
        }
        $zip->close();
        natsort($slides);

        return array_values($slides);
    }
}
