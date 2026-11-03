<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('categories.index', [
            'categories' => $request->user()->categories()->withCount('documents')->orderBy('name')->get(),
            'colors' => Category::COLORS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $request->user()->categories()->create($data + ['slug' => $this->uniqueSlug($request, $data['name'])]);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->user_id === $request->user()->id, 404);
        $category->update($this->validated($request));

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless($category->user_id === $request->user()->id, 404);
        abort_if($category->is_fallback, 422, 'Kategori cadangan tidak bisa dihapus.');

        $fallback = $request->user()->categories()->where('is_fallback', true)->first();
        $category->documents()->update(['category_id' => $fallback?->id]);
        $category->delete();

        return back()->with('success', 'Kategori dihapus, berkasnya dipindah ke "'.($fallback->name ?? 'tanpa kategori').'".');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'color' => ['required', Rule::in(array_keys(Category::COLORS))],
            'keywords' => ['nullable', 'string', 'max:1000'],
        ]);
        $data['keywords'] = collect(explode(',', $data['keywords'] ?? ''))
            ->map(fn ($k) => trim(mb_strtolower($k)))->filter()->unique()->implode(', ');

        return $data;
    }

    private function uniqueSlug(Request $request, string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        for ($i = 2; $request->user()->categories()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
