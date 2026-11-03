<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'keywords', 'is_fallback'];

    protected $casts = ['is_fallback' => 'boolean'];

    /** Warna yang bisa dipilih. */
    public const COLORS = [
        'blue' => '#2563eb',
        'cyan' => '#0891b2',
        'emerald' => '#059669',
        'violet' => '#7c3aed',
        'amber' => '#d97706',
        'orange' => '#ea580c',
        'rose' => '#e11d48',
        'slate' => '#64748b',
    ];

    public const DEFAULTS = [
        ['Surat Masuk', 'blue', 'surat masuk, undangan, permohonan, pemberitahuan, disposisi'],
        ['Surat Keluar', 'cyan', 'surat keluar, surat edaran, edaran, balasan, surat pengantar'],
        ['Keuangan & Faktur', 'emerald', 'invoice, faktur, kwitansi, kuitansi, nota, tagihan, pajak, npwp, rekening, pembayaran, receipt, anggaran, rab, purchase order'],
        ['Kontrak & Legal', 'violet', 'kontrak, perjanjian, mou, agreement, akta, legal, sertifikat, izin, nda'],
        ['Laporan & Notulen', 'amber', 'laporan, report, rekap, rekapitulasi, evaluasi, notulen, notulensi, rapat, minutes'],
        ['SK & Memo', 'orange', 'surat keputusan, keputusan, memo, memorandum, instruksi, sop, kebijakan, peraturan'],
        ['Kepegawaian', 'rose', 'cv, resume, ktp, ijazah, karyawan, pegawai, cuti, gaji, slip gaji, absensi, lamaran, kontrak kerja'],
        ['Lainnya', 'slate', ''],
    ];

    public static function createDefaultsFor(User $user): void
    {
        foreach (self::DEFAULTS as [$name, $color, $keywords]) {
            $user->categories()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'color' => $color,
                'keywords' => $keywords,
                'is_fallback' => $name === 'Lainnya',
            ]);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function hex(): string
    {
        return self::COLORS[$this->color] ?? self::COLORS['slate'];
    }

    /** @return list<string> */
    public function keywordList(): array
    {
        return array_values(array_filter(array_map(
            fn ($k) => mb_strtolower(trim($k)),
            explode(',', (string) $this->keywords)
        )));
    }
}
