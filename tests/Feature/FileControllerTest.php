<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('shared');

        $this->user = User::factory()->create([
            'status' => User::STATUS_UNLOCKED,
        ]);

        // Satisfy middleware: user needs instrument + personal data
        $this->user->instrumentGroups()->attach(
            \App\Models\InstrumentGroup::factory()->create()
        );
        $this->user->personalData()->create([
            'street' => 'Test St 1',
            'city' => 'Test City',
            'zip' => '12345',
            'mobile_phone' => '0123456789',
        ]);
    }

    public function test_index_shows_files_and_folders(): void
    {
        Storage::disk('shared')->put('readme.txt', 'hello');
        Storage::disk('shared')->put('docs/guide.pdf', 'pdf content');

        $response = $this->actingAs($this->user)->get(route('files.index'));

        $response->assertOk();
        $response->assertSee('readme.txt');
        $response->assertSee('docs');
    }

    public function test_browse_subfolder(): void
    {
        Storage::disk('shared')->put('docs/guide.pdf', 'pdf content');

        $response = $this->actingAs($this->user)->get(route('files.browse', 'docs'));

        $response->assertOk();
        $response->assertSee('guide.pdf');
    }

    public function test_download_file(): void
    {
        Storage::disk('shared')->put('readme.txt', 'hello world');

        $response = $this->actingAs($this->user)->get(route('files.download', 'readme.txt'));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');
    }

    public function test_path_traversal_blocked(): void
    {
        Storage::disk('shared')->put('secret.txt', 'secret');

        $response = $this->actingAs($this->user)->get(route('files.browse', '../'));

        $response->assertNotFound();
    }

    public function test_download_nonexistent_file_returns_404(): void
    {
        $response = $this->actingAs($this->user)->get(route('files.download', 'nope.txt'));

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_redirected(): void
    {
        $response = $this->get(route('files.index'));

        $response->assertRedirect();
    }
}
