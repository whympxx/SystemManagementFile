<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use App\Services\AiClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        // Jangan pernah memanggil API sungguhan saat tes
        config(['services.ai.provider' => 'openai', 'services.ai.key' => null, 'services.anthropic.key' => null]);
        Http::preventStrayRequests();
    }

    private function upload(User $user, UploadedFile $file, array $extra = [])
    {
        return $this->actingAs($user)->postJson(route('documents.store'), ['file' => $file] + $extra);
    }

    public function test_register_creates_default_categories(): void
    {
        $this->post(route('register'), [
            'name' => 'Sekretaris', 'email' => 'sek@kantor.id',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $user = User::firstWhere('email', 'sek@kantor.id');
        $this->assertCount(count(Category::DEFAULTS), $user->categories);
        $this->get(route('dashboard'))->assertOk()->assertSee('Halo, Sekretaris');
    }

    public function test_upload_assigns_archive_number_and_keyword_category(): void
    {
        $user = User::factory()->create();

        $res = $this->upload($user, UploadedFile::fake()->createWithContent('Invoice PT Maju 2026.txt', 'Tagihan pembayaran jasa'));

        $res->assertCreated()
            ->assertJsonPath('archive_number', 'ARS/'.now()->format('Y/m').'/0001')
            ->assertJsonPath('category', 'Keuangan & Faktur')
            ->assertJsonPath('classified_by', 'keyword');

        $doc = Document::first();
        Storage::disk('local')->assertExists($doc->path);
        $this->assertSame('Tagihan pembayaran jasa', $doc->content_text);
        $this->assertContains('invoice', $doc->tags->pluck('name'));
        $this->assertContains('2026', $doc->tags->pluck('name'));

        $this->upload($user, UploadedFile::fake()->createWithContent('Notulen rapat.txt', 'isi'))
            ->assertJsonPath('archive_number', 'ARS/'.now()->format('Y/m').'/0002')
            ->assertJsonPath('category', 'Laporan & Notulen');
    }

    public function test_manual_category_is_kept(): void
    {
        $user = User::factory()->create();
        $legal = $user->categories()->where('slug', 'kontrak-legal')->first();

        $this->upload($user, UploadedFile::fake()->createWithContent('invoice.txt', 'invoice'), ['category_id' => $legal->id])
            ->assertJsonPath('category', 'Kontrak & Legal')
            ->assertJsonPath('classified_by', 'manual');
    }

    public function test_unknown_document_goes_to_fallback_category(): void
    {
        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('catatan.txt', 'halo dunia'))
            ->assertJsonPath('category', 'Lainnya');
    }

    public function test_dangerous_file_types_are_rejected(): void
    {
        $user = User::factory()->create();
        foreach (['shell.php', 'page.html', 'logo.svg', 'run.exe'] as $name) {
            $this->upload($user, UploadedFile::fake()->createWithContent($name, '<?php echo 1;'))
                ->assertUnprocessable()->assertJsonValidationErrors('file');
        }
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_users_cannot_access_each_others_documents(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->upload($owner, UploadedFile::fake()->createWithContent('rahasia.txt', 'x'));
        $doc = Document::first();

        $this->actingAs($other);
        $this->get(route('documents.show', $doc))->assertNotFound();
        $this->get(route('documents.download', $doc))->assertNotFound();
        $this->delete(route('documents.destroy', $doc))->assertNotFound();
        $this->post(route('documents.bulk'), ['ids' => [$doc->id], 'action' => 'delete']);
        $this->assertNotSoftDeleted($doc);
        $this->get(route('documents.index'))->assertDontSee('rahasia');
    }

    public function test_search_finds_text_inside_document_and_filters_work(): void
    {
        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('scan001.txt', 'Perjanjian sewa gedung dengan PT Sinar'));
        $this->upload($user, UploadedFile::fake()->createWithContent('foto.txt', 'lain'));
        $legal = $user->categories()->where('slug', 'kontrak-legal')->first();

        $this->actingAs($user)->get(route('documents.index', ['q' => 'Sinar']))
            ->assertSee('scan001')->assertDontSee('>foto<', false);

        $this->get(route('documents.index', ['category' => $legal->id]))
            ->assertSee('scan001')->assertDontSee('foto');

        $this->get(route('documents.index', ['type' => 'pdf']))->assertSee('Tidak ada berkas');
    }

    public function test_trash_restore_and_permanent_delete(): void
    {
        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('a.txt', 'a'));
        $doc = Document::first();

        $this->actingAs($user)->delete(route('documents.destroy', $doc));
        $this->assertSoftDeleted($doc);
        $this->get(route('documents.index', ['trash' => 1]))->assertSee($doc->title);

        $this->post(route('documents.restore', $doc));
        $this->assertNotSoftDeleted($doc);

        $doc->delete();
        $this->post(route('documents.bulk'), ['ids' => [$doc->id], 'action' => 'force']);
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        Storage::disk('local')->assertMissing($doc->path);
    }

    public function test_bulk_move_and_expiring_filter(): void
    {
        $user = User::factory()->create();
        $folder = $user->folders()->create(['name' => 'HRD']);
        $this->upload($user, UploadedFile::fake()->createWithContent('izin usaha.txt', 'x'), ['expires_at' => now()->addDays(10)->toDateString()]);
        $this->upload($user, UploadedFile::fake()->createWithContent('b.txt', 'x'));
        $ids = Document::pluck('id')->all();

        $this->actingAs($user)->post(route('documents.bulk'), ['ids' => $ids, 'action' => 'folder', 'folder_id' => $folder->id]);
        $this->assertSame(2, $folder->documents()->count());

        $this->get(route('documents.index', ['expiring' => 1]))->assertSee('izin usaha')->assertDontSee('>b<', false);
        $this->get(route('dashboard'))->assertSee('izin usaha');
    }

    public function test_preview_serves_text_as_plain_text(): void
    {
        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('catatan.txt', '<script>alert(1)</script>'));

        $this->actingAs($user)->get(route('documents.preview', Document::first()))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_all_pages_render(): void
    {
        $user = User::factory()->create();
        // JPEG 1x1 piksel (tanpa butuh ekstensi GD)
        $jpeg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        $this->upload($user, UploadedFile::fake()->createWithContent('ktp.jpg', $jpeg))->assertCreated();
        $doc = Document::first();
        $cat = $user->categories()->first();

        $this->actingAs($user);
        $this->get(route('documents.create'))->assertOk()->assertSee('lepas berkas di sini');
        $this->get(route('categories.index'))->assertOk()->assertSee('Kategori baru');
        $this->get(route('documents.index', ['view' => 'grid']))->assertOk()->assertSee($doc->archive_number);
        $this->get(route('documents.show', $doc))->assertOk()->assertSee('Kepegawaian');
        $this->get(route('documents.preview', $doc))->assertOk();

        $this->put(route('categories.update', $cat), ['name' => 'Surat Undangan', 'color' => 'rose', 'keywords' => 'Undangan, Rapat'])->assertRedirect();
        $this->assertSame('undangan, rapat', $cat->fresh()->keywords);
        $this->post(route('categories.store'), ['name' => 'Proposal', 'color' => 'blue', 'keywords' => 'proposal']);
        $this->assertDatabaseHas('categories', ['slug' => 'proposal', 'user_id' => $user->id]);

        $this->put(route('documents.update', $doc), ['title' => 'KTP Budi', 'tags' => 'karyawan, Arsip HR'])->assertRedirect();
        $this->assertEqualsCanonicalizing(['karyawan', 'arsip hr'], $doc->fresh()->tags->pluck('name')->all());
    }

    public function test_openai_compatible_provider_parses_reply_and_falls_back_on_errors(): void
    {
        config(['services.ai.key' => 'sk-test', 'services.ai.base_url' => 'https://api.example.test/v1', 'services.ai.model' => 'qwen/test']);
        $reply = "<think>menimbang...</think>\n```json\n".json_encode([
            'category' => 'Kontrak & Legal', 'title' => 'MoU Kerja Sama Vendor', 'summary' => 'Nota kesepahaman dengan vendor.',
            'tags' => ['mou', 'vendor'], 'document_date' => '2026-02-01', 'expires_at' => '2028-02-01',
        ])."\n```";
        Http::fake([
            'api.example.test/v1/chat/completions' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => $reply], 'finish_reason' => 'stop']]])
                ->push(['error' => ['message' => 'quota habis']], 429),
        ]);

        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('dok1.txt', 'Nota kesepahaman'))
            ->assertJsonPath('category', 'Kontrak & Legal')
            ->assertJsonPath('classified_by', 'ai')
            ->assertJsonPath('title', 'MoU Kerja Sama Vendor');

        Http::assertSent(fn ($req) => $req->hasHeader('Authorization', 'Bearer sk-test')
            && $req['model'] === 'qwen/test'
            && $req['response_format']['type'] === 'json_schema');

        // AI gagal -> tetap tersimpan dengan kategori dari kata kunci, alasan dicatat
        $this->upload($user, UploadedFile::fake()->createWithContent('faktur.txt', 'x'))
            ->assertCreated()
            ->assertJsonPath('category', 'Keuangan & Faktur')
            ->assertJsonPath('classified_by', 'keyword');
        $this->assertStringContainsString('Batas pemakaian', Document::latest('id')->first()->analysis_error);
    }

    public function test_ai_result_overrides_keyword_result(): void
    {
        config(['services.ai.key' => 'test-key']);
        $this->app->bind(AiClassifier::class, fn () => new class extends AiClassifier
        {
            public function classify(Collection $categories, string $filename, string $extension, ?string $text, string $absolutePath): array
            {
                return [
                    'category' => 'Kontrak & Legal', 'title' => 'Perjanjian Sewa Gedung 2026',
                    'summary' => 'Perjanjian sewa gedung selama satu tahun.', 'tags' => ['sewa', 'gedung'],
                    'document_date' => '2026-01-05', 'expires_at' => '2027-01-05',
                ];
            }
        });

        $user = User::factory()->create();
        $this->upload($user, UploadedFile::fake()->createWithContent('scan_0042.txt', 'invoice'))
            ->assertJsonPath('category', 'Kontrak & Legal')
            ->assertJsonPath('classified_by', 'ai')
            ->assertJsonPath('title', 'Perjanjian Sewa Gedung 2026');

        $doc = Document::first();
        $this->assertSame('2027-01-05', $doc->expires_at->toDateString());
        $this->assertContains('sewa', $doc->tags->pluck('name'));
        $this->actingAs($user)->get(route('documents.show', $doc))->assertSee('Perjanjian sewa gedung selama satu tahun.');
    }
}
