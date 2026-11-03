@props(['ext', 'size' => 'size-10'])
@php
    $map = [
        'pdf' => ['bg-rose-100 text-rose-700', 'PDF'],
        'doc' => ['bg-blue-100 text-blue-700', 'DOC'], 'docx' => ['bg-blue-100 text-blue-700', 'DOC'], 'odt' => ['bg-blue-100 text-blue-700', 'ODT'], 'rtf' => ['bg-blue-100 text-blue-700', 'RTF'],
        'xls' => ['bg-emerald-100 text-emerald-700', 'XLS'], 'xlsx' => ['bg-emerald-100 text-emerald-700', 'XLS'], 'ods' => ['bg-emerald-100 text-emerald-700', 'ODS'], 'csv' => ['bg-emerald-100 text-emerald-700', 'CSV'],
        'ppt' => ['bg-orange-100 text-orange-700', 'PPT'], 'pptx' => ['bg-orange-100 text-orange-700', 'PPT'], 'odp' => ['bg-orange-100 text-orange-700', 'ODP'],
        'zip' => ['bg-amber-100 text-amber-800', 'ZIP'], 'rar' => ['bg-amber-100 text-amber-800', 'RAR'], '7z' => ['bg-amber-100 text-amber-800', '7Z'],
    ];
    [$color, $label] = $map[$ext] ?? (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? ['bg-violet-100 text-violet-700', 'IMG'] : ['bg-slate-100 text-slate-600', strtoupper(substr($ext, 0, 4))]);
@endphp
<span {{ $attributes->merge(['class' => "$size $color inline-flex shrink-0 items-center justify-center rounded-lg text-[10px] font-bold tracking-wide"]) }}>{{ $label }}</span>
