@props(['category'])
@if ($category)
    <span {{ $attributes->merge(['class' => 'chip']) }} style="background: {{ $category->hex() }}1a; color: {{ $category->hex() }}">
        <span class="size-1.5 rounded-full" style="background: {{ $category->hex() }}"></span>{{ $category->name }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'chip bg-slate-100 text-slate-500']) }}>Belum dikategorikan</span>
@endif
