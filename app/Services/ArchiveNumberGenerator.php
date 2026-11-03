<?php

namespace App\Services;

use App\Models\User;

/**
 * Membuat nomor arsip berurutan per user per bulan, contoh: ARS/2026/11/0007.
 */
class ArchiveNumberGenerator
{
    public function next(User $user, ?\DateTimeInterface $date = null): string
    {
        $date ??= now();
        $prefix = sprintf('%s/%s/%s/', config('archive.prefix'), $date->format('Y'), $date->format('m'));

        $last = $user->documents()
            ->withTrashed()
            ->where('archive_number', 'like', $prefix.'%')
            ->orderByDesc('archive_number')
            ->value('archive_number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
