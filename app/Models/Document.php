<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'folder_id', 'archive_number', 'title', 'original_name', 'path',
        'mime_type', 'extension', 'size', 'description', 'document_date', 'expires_at',
        'content_text', 'ai_summary', 'analysis_status', 'classified_by', 'analysis_error', 'is_starred',
    ];

    protected $hidden = ['content_text'];

    protected $casts = [
        'document_date' => 'date',
        'expires_at' => 'date',
        'is_starred' => 'boolean',
        'size' => 'integer',
    ];

    /** Kelompok jenis file untuk filter. */
    public const TYPE_GROUPS = [
        'pdf' => ['label' => 'PDF', 'ext' => ['pdf']],
        'word' => ['label' => 'Word', 'ext' => ['doc', 'docx', 'odt', 'rtf']],
        'excel' => ['label' => 'Excel', 'ext' => ['xls', 'xlsx', 'ods', 'csv']],
        'powerpoint' => ['label' => 'PowerPoint', 'ext' => ['ppt', 'pptx', 'odp']],
        'image' => ['label' => 'Gambar/Scan', 'ext' => ['jpg', 'jpeg', 'png', 'gif', 'webp']],
        'text' => ['label' => 'Teks', 'ext' => ['txt', 'md']],
        'archive' => ['label' => 'ZIP/RAR', 'ext' => ['zip', 'rar', '7z']],
    ];

    /** Ekstensi yang boleh diunggah (tanpa file yang bisa dieksekusi atau HTML/SVG). */
    public static function allowedExtensions(): array
    {
        return array_merge(...array_column(self::TYPE_GROUPS, 'ext'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function typeGroup(): ?string
    {
        foreach (self::TYPE_GROUPS as $key => $group) {
            if (in_array($this->extension, $group['ext'], true)) {
                return $key;
            }
        }

        return null;
    }

    public function isImage(): bool
    {
        return in_array($this->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    /** Bisa ditampilkan langsung di browser tanpa risiko XSS. */
    public function isPreviewable(): bool
    {
        return $this->isImage() || in_array($this->extension, ['pdf', 'txt', 'md', 'csv'], true);
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(now()->addDays($days));
    }

    public function sizeHuman(): string
    {
        return self::formatBytes($this->size);
    }

    public static function formatBytes(int|float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = (float) $bytes;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return ($i === 0 ? (int) $size : number_format($size, 1, ',', '.')).' '.$units[$i];
    }

    /** Terapkan filter dari query string halaman daftar berkas. */
    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, function (Builder $q, string $term) {
                $like = '%'.$term.'%';
                $q->where(fn (Builder $w) => $w
                    ->where('title', 'like', $like)
                    ->orWhere('original_name', 'like', $like)
                    ->orWhere('archive_number', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('ai_summary', 'like', $like)
                    ->orWhere('content_text', 'like', $like)
                    ->orWhereHas('tags', fn (Builder $t) => $t->where('name', 'like', $like)));
            })
            ->when($f['category'] ?? null, fn (Builder $q, $id) => $id === 'none'
                ? $q->whereNull('category_id')
                : $q->where('category_id', $id))
            ->when($f['folder'] ?? null, fn (Builder $q, $id) => $q->where('folder_id', $id))
            ->when($f['tag'] ?? null, fn (Builder $q, $tag) => $q->whereHas('tags', fn (Builder $t) => $t->where('name', $tag)))
            ->when(isset(self::TYPE_GROUPS[$f['type'] ?? '']), fn (Builder $q) => $q->whereIn('extension', self::TYPE_GROUPS[$f['type']]['ext']))
            ->when($f['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($f['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when(! empty($f['starred']), fn (Builder $q) => $q->where('is_starred', true))
            ->when(! empty($f['expiring']), fn (Builder $q) => $q->whereNotNull('expires_at')
                ->whereDate('expires_at', '<=', now()->addDays(30)));
    }
}
