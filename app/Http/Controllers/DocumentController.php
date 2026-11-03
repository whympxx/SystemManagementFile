<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeDocument;
use App\Models\Document;
use App\Services\AiClassifier;
use App\Services\ArchiveNumberGenerator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    private const SORTS = [
        'newest' => ['created_at', 'desc'],
        'oldest' => ['created_at', 'asc'],
        'title' => ['title', 'asc'],
        'size' => ['size', 'desc'],
        'expires' => ['expires_at', 'asc'],
        'archive' => ['archive_number', 'desc'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->only(['q', 'category', 'folder', 'tag', 'type', 'from', 'to', 'starred', 'expiring']);
        $trash = $request->boolean('trash');
        [$column, $direction] = self::SORTS[$request->input('sort')] ?? self::SORTS['newest'];

        $documents = $user->documents()
            ->when($trash, fn ($q) => $q->onlyTrashed())
            ->with(['category', 'folder', 'tags'])
            ->filter($filters)
            ->orderByRaw($column === 'expires_at' ? 'expires_at IS NULL' : '1')
            ->orderBy($column, $direction)
            ->paginate(24)
            ->withQueryString();

        return view('documents.index', [
            'documents' => $documents,
            'filters' => $filters,
            'trash' => $trash,
            'sort' => $request->input('sort', 'newest'),
            'view' => $request->input('view') === 'grid' ? 'grid' : 'list',
            'categories' => $user->categories()->orderBy('name')->get(),
            'folders' => $user->folders()->orderBy('name')->get(),
            'tags' => $user->tags()->withCount('documents')->orderByDesc('documents_count')->take(30)->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('documents.upload', [
            'categories' => $request->user()->categories()->orderBy('name')->get(),
            'folders' => $request->user()->folders()->orderBy('name')->get(),
            'maxKb' => config('archive.max_upload_kb'),
            'extensions' => Document::allowedExtensions(),
            'aiEnabled' => AiClassifier::enabled(),
        ]);
    }

    /** Menerima satu berkas per request (frontend mengirim banyak berkas secara paralel). */
    public function store(Request $request, ArchiveNumberGenerator $numbers): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'file' => ['required', 'file', 'max:'.config('archive.max_upload_kb'), 'extensions:'.implode(',', Document::allowedExtensions())],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $user->id)],
            'folder_id' => ['nullable', Rule::exists('folders', 'id')->where('user_id', $user->id)],
            'expires_at' => ['nullable', 'date'],
        ], [
            'file.max' => 'Ukuran berkas melebihi batas '.Document::formatBytes(config('archive.max_upload_kb') * 1024).'.',
            'file.extensions' => 'Jenis berkas ini tidak diizinkan.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('documents/'.$user->id.'/'.now()->format('Y/m'), Str::uuid().'.'.$ext, 'local');

        $document = null;
        for ($attempt = 0; $attempt < 3 && ! $document; $attempt++) {
            try {
                $document = $user->documents()->create([
                    'archive_number' => $numbers->next($user),
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'extension' => $ext,
                    'size' => $file->getSize(),
                    'category_id' => $data['category_id'] ?? null,
                    'classified_by' => isset($data['category_id']) ? 'manual' : null,
                    'folder_id' => $data['folder_id'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Upload paralel mendapat nomor yang sama; ambil nomor berikutnya
            }
        }
        abort_unless($document, 500, 'Gagal membuat nomor arsip.');

        AnalyzeDocument::dispatch($document, keepCategory: isset($data['category_id']));

        $document->refresh()->load(['category', 'tags']);

        return response()->json([
            'id' => $document->id,
            'archive_number' => $document->archive_number,
            'title' => $document->title,
            'category' => $document->category?->name,
            'category_color' => $document->category?->hex(),
            'classified_by' => $document->classified_by,
            'tags' => $document->tags->pluck('name'),
            'summary' => $document->ai_summary,
            'status' => $document->analysis_status,
            'url' => route('documents.show', $document),
        ], 201);
    }

    public function show(Request $request, Document $document): View
    {
        $this->own($request, $document);
        $document->load(['category', 'folder', 'tags']);

        return view('documents.show', [
            'document' => $document,
            'categories' => $request->user()->categories()->orderBy('name')->get(),
            'folders' => $request->user()->folders()->orderBy('name')->get(),
            'aiEnabled' => AiClassifier::enabled(),
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->own($request, $document);
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $user->id)],
            'folder_id' => ['nullable', Rule::exists('folders', 'id')->where('user_id', $user->id)],
            'document_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        if (($data['category_id'] ?? null) != $document->category_id) {
            $document->classified_by = 'manual';
        }
        $document->fill(collect($data)->except('tags')->all())->save();

        $tagIds = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn ($t) => mb_substr(trim(mb_strtolower($t)), 0, 50))
            ->filter()->unique()
            ->map(fn ($name) => $user->tags()->firstOrCreate(['name' => $name])->id);
        $document->tags()->sync($tagIds->all());

        return back()->with('success', 'Perubahan disimpan.');
    }

    public function reanalyze(Request $request, Document $document): RedirectResponse
    {
        $this->own($request, $document);
        $document->update(['analysis_status' => 'pending']);
        AnalyzeDocument::dispatch($document, keepCategory: false);

        return back()->with('success', 'Dokumen dianalisis ulang.');
    }

    public function toggleStar(Request $request, Document $document): RedirectResponse|JsonResponse
    {
        $this->own($request, $document);
        $document->update(['is_starred' => ! $document->is_starred]);

        return $request->expectsJson()
            ? response()->json(['starred' => $document->is_starred])
            : back();
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->own($request, $document);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Berkas dipindahkan ke Sampah.');
    }

    public function restore(Request $request, Document $document): RedirectResponse
    {
        $this->own($request, $document);
        $document->restore();

        return back()->with('success', 'Berkas dipulihkan.');
    }

    public function forceDestroy(Request $request, Document $document): RedirectResponse
    {
        $this->own($request, $document);
        $this->purge($document);

        return back()->with('success', 'Berkas dihapus permanen.');
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->own($request, $document);

        return Storage::disk('local')->download($document->path, $this->downloadName($document));
    }

    /** Tampilkan di browser hanya untuk tipe aman (PDF, gambar, teks). */
    public function preview(Request $request, Document $document): StreamedResponse
    {
        $this->own($request, $document);
        abort_unless($document->isPreviewable(), 415);

        $type = in_array($document->extension, ['txt', 'md', 'csv'], true)
            ? 'text/plain; charset=utf-8'
            : $document->mime_type;

        return Storage::disk('local')->response($document->path, $this->downloadName($document), [
            'Content-Type' => $type,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
        ]);
    }

    public function bulk(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', Rule::in(['category', 'folder', 'star', 'delete', 'restore', 'force'])],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $user->id)],
            'folder_id' => ['nullable', Rule::exists('folders', 'id')->where('user_id', $user->id)],
        ]);

        $query = $user->documents()->withTrashed()->whereIn('id', $data['ids']);
        $count = (clone $query)->count();

        match ($data['action']) {
            'category' => $query->update(['category_id' => $data['category_id'] ?? null, 'classified_by' => 'manual']),
            'folder' => $query->update(['folder_id' => $data['folder_id'] ?? null]),
            'star' => $query->update(['is_starred' => true]),
            'delete' => $query->get()->each->delete(),
            'restore' => $query->onlyTrashed()->get()->each->restore(),
            'force' => $query->onlyTrashed()->get()->each(fn (Document $d) => $this->purge($d)),
        };

        return back()->with('success', "{$count} berkas diperbarui.");
    }

    private function purge(Document $document): void
    {
        Storage::disk('local')->delete($document->path);
        $document->forceDelete();
    }

    private function downloadName(Document $document): string
    {
        $base = Str::of($document->title)->replaceMatches('/[\\\\\/:*?"<>|]+/', ' ')->squish()->limit(150, '');

        return ($base->isEmpty() ? 'dokumen' : $base).'.'.$document->extension;
    }

    private function own(Request $request, Document $document): void
    {
        abort_unless($document->user_id === $request->user()->id, 404);
    }
}
